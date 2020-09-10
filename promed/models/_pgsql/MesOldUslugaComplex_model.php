<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * MesOldUslugaComplex_model - модель для работы с группировщиком КСГ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package            Common
 * @access            public
 * @copyright        Copyright (c) 2019 Swan Ltd.
 * @author            Dmitry Vlasenko
 */
class MesOldUslugaComplex_model extends EvnSection_model
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Вспомогательная фукнция для сортировки массива КСГ по Diag_id.
	 */
	function sortKSGArrayByDiagId($a, $b) {
		if ($a['Diag_id'] == $b['Diag_id']) {
			return 0;
		}
		return ($a['Diag_id'] > $b['Diag_id']) ? -1 : 1;
	}

	/**
	 * Общий алгоритм определения КСГ/КПГ/Коэффициента КСГ с 2019 года
	 */
	function getKSGKPGKOEFF($data)
	{
		$combovalues = array();

		$EvnSection_TotalFract = 0;
		$KSGFullArray = array();
		$KSGList = array();
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

		// считаем длительность пребывания
		$datediff = strtotime($data['EvnSection_disDate']) - strtotime($data['EvnSection_setDate']);
		$data['Duration'] = floor($datediff / (60 * 60 * 24));
		// для Астрахани: Расчёт длительности при определении КСГ для круглосуточного и дневного стационара выполняется следующим образом: Дата выписки – Дата поступления + 1 день.
		if (getRegionNick() == 'astra' || in_array($data['LpuUnitType_id'], array('6', '7', '9'))) {
			$data['Duration'] += 1; // для дневного +1
		}

		// если движение из предыдущего года, то связки берём на дату последнего движения КВС
		if (!empty($data['LastEvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['LastEvnSection_disDate'];
		}

		$isAlgo2020 = false;
		if (
			(getRegionNick() == 'krym' && $data['EvnSection_disDate'] >= '2019-12-25')
			|| $data['EvnSection_disDate'] >= '2020-01-01'
		) {
			$isAlgo2020 = true;
		}

		$mesTypeFilter = " and mo.MesType_id IN (10, 14)";
		$data['MesPayType_id'] = 10;
		if (in_array($data['LpuUnitType_id'], array('6', '7', '9'))) {
			$mesTypeFilter = " and mo.MesType_id IN (9, 13)";
			$data['MesPayType_id'] = 9;
		}

		$filter_paytype = "and eu.PayType_id = {$this->getPayTypeIdBySysNick('oms')}";
		if (getRegionNick() == 'perm' and !empty($data['PayType_id'])) {
			// Если вид оплаты МВД, то фильтруем услуги по типу оплаты ОМС и МВД, иначе только ОМС
			switch($data['PayType_id']) {
				case $this->getPayTypeIdBySysNick('ovd'):
					$filter_paytype = " and eu.PayType_id IN ({$this->getPayTypeIdBySysNick('oms')}, {$this->getPayTypeIdBySysNick('ovd')})";
					break;
				case $this->getPayTypeIdBySysNick('bud'):
					$filter_paytype = " and eu.PayType_id IN ({$this->getPayTypeIdBySysNick('oms')}, {$this->getPayTypeIdBySysNick('bud')})";
					break;
				case $this->getPayTypeIdBySysNick('fbud'):
					$filter_paytype = " and eu.PayType_id IN ({$this->getPayTypeIdBySysNick('oms')}, {$this->getPayTypeIdBySysNick('fbud')})";
					break;
				case $this->getPayTypeIdBySysNick('mbudtrans'):
					$filter_paytype = " and eu.PayType_id IN ({$this->getPayTypeIdBySysNick('oms')},{$this->getPayTypeIdBySysNick('mbudtrans')})";
					break;
			}
		}
		// Для Вологды вид оплаты услуг должен соответствовать виду оплаты в движении
		// @task https://redmine.swan-it.ru/issues/187070
		// Для Пензы тоже https://jira.is-mis.ru/browse/PROMEDWEB-4441
		if (in_array(getRegionNick(), ['penza', 'vologda', 'yaroslavl']) && !empty($data['PayType_id'])) {
			$filter_paytype = " and eu.PayType_id = :PayType_id";
		}

		// получаем список сгруппированных движений, диагнозы и услуги будем брать из всех движений группы
		if (!empty($data['EvnSectionIds'])) {
			$EvnSectionIds = $data['EvnSectionIds'];
		} else {
			$EvnSectionIds = array();
			if (!empty($data['EvnSection_id'])) {
				$EvnSectionIds[] = $data['EvnSection_id'];
			}
		}

		$query = "
			select
				dbo.AgeTFOMS(PS.Person_BirthDay, CAST(:EvnSection_setDate AS date)) as \"Person_Age\",
				datediff('day', PS.Person_BirthDay, CAST(:EvnSection_setDate AS date)) as \"Person_AgeDays\",
				PS.Sex_id as \"Sex_id\",
				case
					when date_part('month', PS.Person_BirthDay) = date_part('month', CAST(:EvnSection_setDate AS date)) and date_part('day', PS.Person_BirthDay) = date_part('day', CAST(:EvnSection_setDate AS date))
						then 1
						else 0
				end as \"BirthToday\"
			from
				v_PersonState PS
			where
				Person_id = :Person_id
		";
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
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

		if (isset($data['UslugaComplexIds']) && isset($data['UslugaComplexCodes'])) {
			$UslugaComplexIds = $data['UslugaComplexIds'];
			$UslugaComplexCodes = $data['UslugaComplexCodes'];
		} else {
			$UslugaComplexIds = array();
			$UslugaComplexCodes = array();
			// получаем все услуги группы с видом оплаты ОМС
			if (!empty($EvnSectionIds)) {
				$query = "
					select distinct
						uc.UslugaComplex_id as \"UslugaComplex_id\",
						uc.UslugaComplex_Code as \"UslugaComplex_Code\"
					from
						v_EvnUsluga eu
						inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
					where
						cast(eu.EvnUsluga_pid as varchar) IN ('" . implode("','", $EvnSectionIds) . "')
						{$filter_paytype}
						and eu.EvnUsluga_setDT is not null
						and eu.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaPar', 'EvnUslugaStom')
				";
				$result = $this->db->query($query, $data);

				if (is_object($result)) {
					$resp = $result->result('array');
					foreach ($resp as $respone) {
						$UslugaComplexIds[] = $respone['UslugaComplex_id'];
						$UslugaComplexCodes[] = $respone['UslugaComplex_Code'];
					}
				}
			}
		}

		if (getRegionNick() == 'khak' ){
			$filter_uslugacategory = "'gost2011','tfoms'";
		}else{
			$filter_uslugacategory = "'gost2011'";
		}
		
		$data['EvnUsluga_IVLHours'] = 0;
		if (!empty($EvnSectionIds)) {
			$query = "
				select
					eu.EvnUsluga_setDT as \"EvnUsluga_setDT\",
					eu.EvnUsluga_disDT as \"EvnUsluga_disDT\"
				from
					v_EvnUsluga eu
					inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
					left join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id
				where
					cast(eu.EvnUsluga_pid as varchar) IN ('" . implode("','", $EvnSectionIds) . "')
					{$filter_paytype}
					and  ucat.UslugaCategory_SysNick  in ({$filter_uslugacategory})
					and eu.EvnUsluga_setDT is not null
					and eu.EvnUsluga_disDT is not null
					and eu.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaPar', 'EvnUslugaStom')
					and exists(
						select
							uca.UslugaComplexAttribute_id
						from
							v_UslugaComplexAttribute uca
						where
							uca.UslugaComplex_id = eu.UslugaComplex_id
							and uca.UslugaComplexAttributeType_id = :UslugaComplexAttributeType_id
						limit 1
					)
			";
			$result = $this->db->query($query, array(
				'UslugaComplexAttributeType_id' => $this->getUslugaComplexAttributeTypeIdBySysNick('ivl'),
				'PayType_id' => $data['PayType_id']
			));

			if (is_object($result)) {
				$resp = $result->result('array');
				foreach ($resp as $respone) {
					
					$duration = strtotime($respone['EvnUsluga_disDT']) - strtotime($respone['EvnUsluga_setDT']);
					if (getRegionNick() == 'perm') {
						if ($duration > $data['EvnUsluga_IVLHours']) {
							$data['EvnUsluga_IVLHours'] = $duration;
						}
					} else {
						$data['EvnUsluga_IVLHours'] += $duration;
					}
				}

				if (!empty($data['EvnUsluga_IVLHours'])) {
					// приводим к часам
					$data['EvnUsluga_IVLHours'] = floor($data['EvnUsluga_IVLHours'] / 3600);
				}
			}
		}

		$DrugTherapySchemeFilter = "and mu.DrugTherapyScheme_id IS NULL";
		if (!empty($data['DrugTherapyScheme_ids'])) {
			$DrugTherapySchemeFilter = "and (mu.DrugTherapyScheme_id IN ('" . implode("','", $data['DrugTherapyScheme_ids']) . "') OR mu.DrugTherapyScheme_id IS NULL)";
		}

		$MesDopFilter = "and mu.MesDop_id IS NULL";
		if (!empty($data['MesDop_ids'])) {
			$MesDopFilter = "and (mu.MesDop_id IN ('" . implode("','", $data['MesDop_ids']) . "') OR mu.MesDop_id IS NULL)";
		}

		$UslugaComplexFilters = "
			and mu.UslugaComplex_aid is null
			and mu.UslugaComplex_bid is null
		";
		if (!empty($UslugaComplexIds)) {
			$UslugaComplexFilters = "
				and (mu.UslugaComplex_aid IS NULL or mu.UslugaComplex_aid IN ('" . implode("','", $UslugaComplexIds) . "'))
				and (mu.UslugaComplex_bid IS NULL or mu.UslugaComplex_bid IN ('" . implode("','", $UslugaComplexIds) . "'))
			";
		}

		if (isset($data['SoputDiagIds']) && isset($data['SoputDiagCodes'])) {
			$DiagNids = $data['DiagNids'];
			$DiagOids = $data['DiagOids'];
			$SoputDiagIds = $data['SoputDiagIds'];
			$SoputDiagCodes = $data['SoputDiagCodes'];
		} else {
			$DiagNids = array();
			$DiagOids = array();
			$SoputDiagIds = array();
			$SoputDiagCodes = array();
			// получаем все сопутствующие диагнозы группы
			if (!empty($EvnSectionIds)) {
				$query = "
					select distinct
						d.Diag_id as \"Diag_id\",
						d.Diag_Code as \"Diag_Code\",
						edps.DiagSetClass_id as \"DiagSetClass_id\"
					from
						v_EvnDiagPS edps
						inner join v_Diag d on d.Diag_id = edps.Diag_id
					where
						edps.DiagSetClass_id IN (2,3)
						and	cast(edps.EvnDiagPS_pid as varchar) IN ('" . implode("','", $EvnSectionIds) . "')
				";
				$result = $this->db->query($query, $data);

				if (is_object($result)) {
					$resp = $result->result('array');
					foreach ($resp as $respone) {
						if ($respone['DiagSetClass_id'] == 3) {
							$DiagNids[] = $respone['Diag_id'];
						}
						if ($respone['DiagSetClass_id'] == 2) {
							$DiagOids[] = $respone['Diag_id'];
						}
						$SoputDiagIds[] = $respone['Diag_id'];
						$SoputDiagCodes[] = $respone['Diag_Code'];
					}
				}
			}
		}

		if (!$isAlgo2020) {
			$DiagNids = $SoputDiagIds; // до 2020 года в Diag_nid учитывались и сопутствующие и осложнения основного
		}

		$kpgfields = "
			,null as \"KPG\"
			,null as \"Mes_kid\"
		";
		$kpgjoin = "";
		if (in_array(getRegionNick(), array('kareliya', 'ufa'))) {
			$kpgfields = "
				,mokpg.KPG as \"KPG\"
				,mokpg.Mes_kid as \"Mes_kid\"
			";
			$kpgjoin = "
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
			";
		}

		$needKSG = true;
		if (getRegionNick() == 'ufa' && in_array($data['LpuSectionProfile_Code'], array('1004', '1054', '2004', '2054', '4031', '5031', '6031', '4028', '5028', '6028'))) {
			$needKSG = false;
		}

		if ($needKSG && !in_array($data['LpuUnitType_id'], array('6', '7', '9'))) {
			// Определение КСГ при политравме, только для круглосуточного стаца
			if (!empty($SoputDiagCodes)) {
				// в сопутствующий диагнозах есть один из диагнозов : J94.2, J94.8, J94.9, J93, J93.0, J93.1, J93.8, J93.9, J96.0, N17, T79.4, R57.1, R57.8
				$poliSoputDiagExist = false;
				foreach ($SoputDiagCodes as $Diag_Code) {
					if (in_array($Diag_Code, array('J94.2', 'J94.8', 'J94.9', 'J93', 'J93.0', 'J93.1', 'J93.8', 'J93.9', 'J96.0', 'N17', 'N17.0', 'N17.1', 'N17.2', 'N17.8', 'N17.9', 'T79.4', 'R57.1', 'R57.8'))) {
						$poliSoputDiagExist = true;
					}
				}

				$SoputDiagIdsWithOsn = $SoputDiagIds;
				if (!empty($data['Diag_id']) && !in_array($data['Diag_id'], $SoputDiagIdsWithOsn)) {
					$SoputDiagIdsWithOsn[] = $data['Diag_id'];
				}

				if ($poliSoputDiagExist) {
					// 1. Получаем код анатомической области для основного диагноза
					$query = "
						select
							pt.PolyTrauma_Code as \"PolyTrauma_Code\",
							SOPUT.Diag_id as \"Diag_id\"
						from
							v_PolyTrauma pt
							left join lateral(
								select
									pt2.Diag_id
								from
									v_PolyTrauma pt2
								where
									pt2.Diag_id IN ('" . implode("','", $SoputDiagIds) . "')
									and pt2.PolyTrauma_Code <> pt.PolyTrauma_Code
									and pt2.PolyTrauma_begDT <= :EvnSection_disDate
									and (coalesce(pt2.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								limit 1
							) SOPUT on true
						where
							pt.Diag_id IN ('" . implode("','", $SoputDiagIdsWithOsn) . "')
							and pt.PolyTrauma_begDT <= :EvnSection_disDate
							and (coalesce(pt.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						order by
							SOPUT.Diag_id desc,  -- в первую очередь с заполненным диагнозом
							case when pt.PolyTrauma_Code = 7 then 1 else 0 end desc -- в первую очередь с кодом 7
						limit 1
					";

					$result = $this->db->query($query, $data);

					if (is_object($result)) {
						$resp = $result->result('array');
						if (count($resp) > 0) {
							if ($resp[0]['PolyTrauma_Code'] == 7 || !empty($resp[0]['Diag_id'])) {
								$query = "
									select
										mo.Mes_Code as \"Mes_Code\",
										mo.Mes_Name as \"Mes_Name\",
										mo.MesOld_Num as \"MesOld_Num\",
										mo.Mes_id as \"Mes_id\",
										mt.MesTariff_Value as \"MesTariff_Value\",
										coalesce(moc.MesOldCoeff_Coeff, 1) * mt.MesTariff_Value as \"ItogCoeff\",
										mt.MesTariff_id as \"MesTariff_id\",
										mo.MesType_id as \"MesType_id\"
										{$kpgfields}
									from
										v_MesOld mo
										left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
										left join dbo.v_MesOldCoeff moc on moc.Mes_id = mo.Mes_id -- Управленческий коэффициент
										{$kpgjoin}
									where
										mo.MesOld_Num = 'st29.007'
										{$mesTypeFilter}
										and mo.Mes_begDT <= :EvnSection_disDate
										and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
										and mt.MesTariff_begDT <= :EvnSection_disDate
										and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								  	limit 1
								";

								$result = $this->db->query($query, $data);
								if (is_object($result)) {
									$resp_ksg = $result->result('array');
									if (count($resp_ksg) > 0) {
										if (getRegionNick() == 'perm' || !empty($data['getFullArray'])) {
											foreach ($resp_ksg as $one_ksg) {
												$one_ksg['Mes_tid'] = $one_ksg['Mes_id'];
												$one_ksg['Mes_sid'] = null;
												$KSGFullArray[] = $one_ksg;
											}
										}
										$KSGFromPolyTrauma = $resp_ksg[0];
									}
								}
							}
						}
					}
				}
			}
		}

		// Достаём список КСГ по реабилитации
		$MesReabilIds = array();
		if (in_array(getRegionNick(), array('buryatiya', 'perm', 'kareliya', 'krym'))) {
			// Отнесение группы движений осуществляется к КСГ, определенной по услуге, если на ДКЛ КСГ имеет действующую связь с КПГ 37 «Медицинская реабилитация» (стыковка производится в таблице «dbo.MesLink»)
			// Независимо от того, какая КСГ определилась по коду диагноза.
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
			foreach ($resp_reabil as $one_reabil) {
				$MesReabilIds[] = $one_reabil['Mes_id'];
			}
		}

		// Определение КСГ с учётом указанного кол-ва фракций проведения лучевой терапии
		$fractTempTable = "";
		if (!empty($EvnSectionIds) && !empty($UslugaComplexIds)) {
			if (getRegionNick() == 'perm') {
				$query = "
					with onkousl as (
						select
							euob.OnkoRadiotherapy_id,
							euob.UslugaComplex_id,
							euob.EvnUslugaOnkoBeam_CountFractionRT as CountFractionRT
						from
							v_EvnUslugaOnkoBeam euob
						where
							euob.UslugaComplex_id IN ('" . implode("','", $UslugaComplexIds) . "')
							and	euob.EvnUslugaOnkoBeam_pid IN ('" . implode("','", $EvnSectionIds) . "')
												
						union all
													
						select
							euog.OnkoRadiotherapy_id,
							euog.UslugaComplex_id,
							euog.EvnUslugaOnkoGormun_CountFractionRT as CountFractionRT
						from
							v_EvnUslugaOnkoGormun euog
						where
							euog.UslugaComplex_id IN ('" . implode("','", $UslugaComplexIds) . "')
							and	euog.EvnUslugaOnkoGormun_pid IN ('" . implode("','", $EvnSectionIds) . "')
							and euog.EvnUslugaOnkoGormun_IsDrug = 2
							and euog.EvnUslugaOnkoGormun_IsBeam = 2
					)
					
					select
						o1.UslugaComplex_id as \"UslugaComplex_id\",
						COALESCE(o2.CountFractionRT, o1.CountFractionRT, 0) as \"CountFractionRT\"
					from
						onkousl o1
						left join lateral (
							select
								SUM(dist.CountFractionRT) as CountFractionRT
							from
							(
								select distinct
									CountFractionRT
								from
									onkousl o2
								where
									o2.OnkoRadiotherapy_id = o1.OnkoRadiotherapy_id
							) as dist
						) o2 on true
				";
				$resp_fract = $this->queryResult($query);
			} else {
				$query = "select
						UslugaComplex_id as \"UslugaComplex_id\",
						SUM(coalesce(CountFractionRT, 0)) as \"CountFractionRT\"
					from
						(
							select
								UslugaComplex_id,
								EvnUslugaOnkoBeam_CountFractionRT as CountFractionRT
							from
								v_EvnUslugaOnkoBeam euob
							where
								euob.UslugaComplex_id IN ('" . implode("','", $UslugaComplexIds) . "')
								and	euob.EvnUslugaOnkoBeam_pid IN ('" . implode("','", $EvnSectionIds) . "')
							
							union all
								
							select
								UslugaComplex_id,
								EvnUslugaOnkoGormun_CountFractionRT as CountFractionRT
							from
								v_EvnUslugaOnkoGormun euog
							where
								euog.UslugaComplex_id IN ('" . implode("','", $UslugaComplexIds) . "')
								and	euog.EvnUslugaOnkoGormun_pid IN ('" . implode("','", $EvnSectionIds) . "')
						) onkousl
						
					group by UslugaComplex_id";
				$resp_fract = $this->queryResult($query);
			}

			if (!empty($resp_fract)) {
				$fractTempTable = "
					with FRACTION as (
						{$query}
					)
				";
				foreach ($resp_fract as $one_fract) {
					$EvnSection_TotalFract += $one_fract['CountFractionRT'];
				}
			}
		}

		$dopFields = "";
		$dopFieldsPerm = "";
		if (!$isAlgo2020) {
			$dopFields .= "
				, case when mu.MesOldUslugaComplex_SofaScalePoints is not null or mu.MesOldUslugaComplex_IVLHours is not null then 1 else 0 end as \"hasDopCriteria\"
			";
			$orderBy = "
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
				+ case when mu.MesOldUslugaComplex_FracFrom is not null or mu.MesOldUslugaComplex_FracTo is not null then 1 else 0 end -- считаются как 1 критерий
				+ case when mu.MesOldUslugaComplex_SofaScalePoints is not null or mu.MesOldUslugaComplex_IVLHours is not null then 1 else 0 end desc, -- считаются как 1 критерий
			";

			if (getRegionNick() == 'perm') {
				$orderBy = "
					case when mu.MesOldUslugaComplex_Duration is not null or mu.MesOldUslugaComplex_DurationTo is not null then 1 else 0 end desc,
				";
			}
		} else {
			if (getRegionNick() == 'perm'){
				$dopFieldsPerm='when 
					(SUBSTRING(mo.MesOld_Num,1,9)= \'st36.003.\' or SUBSTRING(mo.MesOld_Num,1,9)= \'ds36.004.\') and CAST(SUBSTRING(mo.MesOld_Num,10,12) AS INTEGER) <= \'17\'
				then 1';
			}

			$dopFields .= "
				, mu.Diag_id as \"Diag_id\"
				, mu.DrugTherapyScheme_id as \"DrugTherapyScheme_id\"
				, mu.MesOldUslugaComplex_FracFrom as \"MesOldUslugaComplex_FracFrom\"
				, mu.MesOldUslugaComplex_FracTo as \"MesOldUslugaComplex_FracTo\"
				, mu.MesAgeGroup_id as \"MesAgeGroup_id\"
				, mu.Sex_id as \"Sex_id\"
				, mu.MesOldUslugaComplex_Duration as \"MesOldUslugaComplex_Duration\"
				, mu.MesOldUslugaComplex_DurationTo as \"MesOldUslugaComplex_DurationTo\"
				, mu.MesOldUslugaComplex_SofaScalePoints as \"MesOldUslugaComplex_SofaScalePoints\"
				, mu.MesOldUslugaComplex_IVLHours as \"MesOldUslugaComplex_IVLHours\"
				, mu.MesDop_id as \"MesDop_id\"
				, case
					when mu.RehabScale_id is not null then 1
					when mu.UslugaComplex_aid is not null and mu.UslugaComplex_bid is not null then 1
					when mu.Diag_id in (6363, 6424, 6426, 11628) and mu.MesAgeGroup_id = 4 then 1
					{$dopFieldsPerm}
					else 0
				end as \"hasDopCriteria\"
			";

			if (getRegionNick() == 'yaroslavl') {
				$dopFields .= "
					, case when mo.MesType_id not in (13, 14) then mo.Mes_id else null end as \"Mes_tid\"
					, case when mo.MesType_id in (13, 14) then mo.Mes_id else null end as \"Mes_sid\"				
				";
			} else {
				$dopFields .= "
					, case when mu.UslugaComplex_id is null then mo.Mes_id else null end as \"Mes_tid\"
					, case when mu.UslugaComplex_id is not null then mo.Mes_id else null end as \"Mes_sid\"				
				";
			}
			
			$orderBy = "";
		}

		if (!$isAlgo2020 && in_array(getRegionNick(), array('pskov', 'vologda'))) {
			$dopFields .= "
				, case when mu.DrugTherapyScheme_id is not null then 1 else 0 end
				+ case when mu.RehabScale_id is not null then 1 else 0 end
				+ case when mu.UslugaComplex_aid is not null or mu.UslugaComplex_bid is not null then 1 else 0 end -- считаются как 1 критерий
				+ case when mu.MesOldUslugaComplex_SofaScalePoints is not null or mu.MesOldUslugaComplex_IVLHours is not null then 1 else 0 end as \"criteriaCount\"
			";
		}

		if (!empty($fractTempTable)) {
			$FractionJoin = "left join FRACTION fr on fr.\"UslugaComplex_id\" = mu.UslugaComplex_id";
			$FractionFilter = "
				and (mu.MesOldUslugaComplex_FracFrom <= fr.\"CountFractionRT\" OR mu.MesOldUslugaComplex_FracFrom IS NULL)
				and (mu.MesOldUslugaComplex_FracTo >= fr.\"CountFractionRT\" OR mu.MesOldUslugaComplex_FracTo IS NULL)
			";
			$dopFields .= " , fr.\"CountFractionRT\" as \"EvnSection_TotalFract\"";
		} else {
			$FractionJoin = "";
			$FractionFilter = "
				and mu.MesOldUslugaComplex_FracFrom IS NULL
				and mu.MesOldUslugaComplex_FracTo IS NULL
			";
			$dopFields .= " , null as \"EvnSection_TotalFract\"";
		}
		
		$LpuLevelFilter = "";
		if (getRegionNick() == 'penza') {
			$data['LpuLevel'] = 0;
			// достаём значение из тарифа "Уровень МО"
			$resp_ur = $this->queryResult("
				select
					cast(av.AttributeValue_ValueFloat as int) as \"LpuLevel\"
				from
					v_AttributeVision avis
					inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
					inner join lateral (
						select
							av2.AttributeValue_ValueString
						from
							v_AttributeValue av2
							inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.Lpu'
							and coalesce(av2.AttributeValue_ValueIdent,:Lpu_id) = :Lpu_id
						limit 1
					) MOFILTER on true
				where
					avis.AttributeVision_TableName = 'dbo.TariffClass'
					and avis.AttributeVision_TablePKey = (select TariffClass_id from v_TariffClass where TariffClass_SysNick = 'level' limit 1)
					and avis.AttributeVision_IsKeyValue = 2
					and coalesce(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
					and coalesce(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
				limit 1
			", array(
				'EvnSection_disDate' => $data['EvnSection_disDate'],
				'Lpu_id' => $data['Lpu_id']
			));

			if (!empty($resp_ur[0]['LpuLevel'])) {
				switch ($resp_ur[0]['LpuLevel']) {
					case 1:
						$data['LpuLevel'] = 1;
						break;
					case 2:
					case 3:
						$data['LpuLevel'] = 2;
						break;
					case 4:
					case 5:
					case 6:
						$data['LpuLevel'] = 3;
						break;
				}
			}
			
			$LpuLevelFilter = "and coalesce(mu.MesOldUslugaComplex_LpuLevel, :LpuLevel) = :LpuLevel";
		}

		$LpuSectionProfileFilter = "";
		if (in_array(getRegionNick(), ['buryatiya', 'penza'])) {
			$LpuSectionProfileFilter = "and coalesce(mu.LpuSectionProfile_id, :LpuSectionProfile_id) = :LpuSectionProfile_id";
		}

		// 1. Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
		if (!$isAlgo2020 && $needKSG && !empty($UslugaComplexIds) && !$KSGFromPolyTrauma) {
			$diagNidFilter = "and mu.Diag_nid IS NULL";
			if (!empty($DiagNids)) {
				$diagNidFilter = "and (mu.Diag_nid IS NULL OR mu.Diag_nid IN ('" . implode("','", $DiagNids) . "'))";
			}
			$diagOidFilter = "and mu.Diag_oid IS NULL";
			if (!empty($DiagOids)) {
				$diagOidFilter = "and (mu.Diag_oid IS NULL OR mu.Diag_oid IN ('" . implode("','", $DiagOids) . "'))";
			}

			$query = "
				{$fractTempTable}
						
				select
					uc.UslugaComplex_id as \"UslugaComplex_id\",
					mu.MesOldUslugaComplex_id as \"MesOldUslugaComplex_id\",
					mo.Mes_Code as \"Mes_Code\",
					mo.Mes_Name as \"Mes_Name\",
					mo.MesOld_Num as \"MesOld_Num\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"MesTariff_Value\",
					coalesce(moc.MesOldCoeff_Coeff, 1) * mt.MesTariff_Value as \"ItogCoeff\",
					mt.MesTariff_id as \"MesTariff_id\",
					mo.MesType_id as \"MesType_id\",
					mu.Diag_nid as \"Diag_nid\",
					mu.Diag_oid as \"Diag_oid\",
					mu.Diag_id as \"Diag_id\",
					case when coalesce(mu.MesOldUslugaComplex_Duration, mu.MesOldUslugaComplex_DurationTo) is not null then 1 else 0 end as \"WithDuration\"
					{$kpgfields}
					{$dopFields}
				from
					v_UslugaComplex uc
					inner join v_MesOldUslugaComplex mu on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					{$FractionJoin}
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					left join dbo.v_MesOldCoeff moc on moc.Mes_id = mo.Mes_id -- Управленческий коэффициент
					{$kpgjoin}
				where
					uc.UslugaComplex_id IN ('" . implode("','", $UslugaComplexIds) . "')
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
					{$diagNidFilter}
					{$diagOidFilter}
					and (mu.Sex_id = :Sex_id OR Sex_id IS NULL)
					and (
						(:Person_Age >= 18 and mu.MesAgeGroup_id = 1)
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 2)
						or (:Person_AgeDays > 28 and mu.MesAgeGroup_id = 3)
						or (:Person_AgeDays <= 28 and mu.MesAgeGroup_id = 4) -- 1 от 0 до 28 дней включительно
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 5) -- 4 от 0 дней до 18 лет НЕ включительно
						or (:Person_Age >= 18 and mu.MesAgeGroup_id = 6) -- 5 18 лет и старше
						or (:Person_Age < 8 and mu.MesAgeGroup_id = 7)
						or (:Person_Age >= 8 and mu.MesAgeGroup_id = 8)
						or (:Person_AgeDays >= 29 and :Person_AgeDays <= 90 and mu.MesAgeGroup_id = 9) -- 2 от 29 до 90 дней включительно
						or (:Person_AgeDays >= 91 and :Person_Age < 1 and mu.MesAgeGroup_id = 10) -- 3 от 91 дня до 1 года НЕ включительно
						{$MesAgeGroup11Filter}
						or (mu.MesAgeGroup_id IS NULL)
					)
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and (mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL)
					{$DrugTherapySchemeFilter}
					{$MesDopFilter}
					and (mu.RehabScale_id = :RehabScale_id OR mu.RehabScale_id IS NULL)
					{$UslugaComplexFilters}
					and (mu.MesOldUslugaComplex_SofaScalePoints <= :EvnSection_SofaScalePoints OR mu.MesOldUslugaComplex_SofaScalePoints IS NULL)
					and (mu.MesOldUslugaComplex_IVLHours <= :EvnUsluga_IVLHours OR mu.MesOldUslugaComplex_IVLHours IS NULL)
					{$FractionFilter}
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (coalesce(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					{$mesTypeFilter}
					and coalesce(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
					{$LpuSectionProfileFilter}
					{$LpuLevelFilter}
				order by
					uc.UslugaComplex_id,
					case when mu.Diag_id is not null and mu.MesOldUslugaComplex_IsDiag = 2 then 1 else 0 end desc, -- по диагнозу берём в первую очередь
					{$orderBy}
					\"ItogCoeff\" desc
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					if (getRegionNick() == 'perm' || !empty($data['getFullArray'])) {
						foreach ($resp as $one_ksg) {
							$one_ksg['Mes_sid'] = $one_ksg['Mes_id'];
							$one_ksg['Mes_tid'] = null;
							$KSGFullArray[] = $one_ksg;
						}
					}

					// для каждой услуги оставляем только КСГ с наибольшим кол-вом критериев.
					$KSGOperUslArray = array();
					foreach ($resp as $KSGOperOne) {
						if (empty($CurUsluga) || $CurUsluga != $KSGOperOne['UslugaComplex_id']) {
							$CurUsluga = $KSGOperOne['UslugaComplex_id'];

							$KSGOperUslArray[] = $KSGOperOne;
						}
					}

					// сортируем по диагнозу, в первую очередь должны сравниваться между собой КСГ с одинаковыми диагнозами, иначе может быть не учтена длительность при одинаковых диагнозах.
					usort($KSGOperUslArray, array($this, "sortKSGArrayByDiagId"));

					// ищем максимальную КСГ среди разных услуг.
					foreach ($KSGOperUslArray as $KSGOperOne) {
						if (getRegionNick() == 'ufa') {
							if (!empty($KSGOper)) {
								if ($KSGOperOne['ItogCoeff'] > $KSGOper['ItogCoeff']) {
									// берём только если нет связки в MesLink
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
										'Mes_id' => $KSGOperOne['Mes_id'],
										'Mes_sid' => $KSGOper['Mes_id'],
										'EvnSection_disDate' => $data['EvnSection_disDate']
									));

									if (empty($data['MesLink_id'])) {
										$KSGOper = $KSGOperOne;
									}
								} else {
									// если есть связка в MesLink то всё равно берём
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
										'Mes_id' => $KSGOper['Mes_id'],
										'Mes_sid' => $KSGOperOne['Mes_id'],
										'EvnSection_disDate' => $data['EvnSection_disDate']
									));

									if (!empty($data['MesLink_id'])) {
										$KSGOper = $KSGOperOne;
									}
								}
							} else {
								$KSGOper = $KSGOperOne;
							}
						} else if (getRegionNick() == 'perm') {
							if (
								// сраниваем длительность/коэфф.
								((empty($KSGOperOne['Diag_id']) || empty($KSGOper['Diag_id']) || $KSGOperOne['Diag_id'] != $KSGOper['Diag_id']) && $KSGOperOne['ItogCoeff'] > $KSGOper['ItogCoeff']) // если диагнозы не совпадают или без учета диагноза определена КСГ, берём с наибольшим коэфф
								|| (!empty($KSGOperOne['Diag_id']) && $KSGOperOne['Diag_id'] == $KSGOper['Diag_id'] && !empty($KSGOperOne['WithDuration']) && empty($KSGOper['WithDuration'])) // если диагнозы совпадают, берём тот где учитывается длительность лечения
								|| (!empty($KSGOperOne['Diag_id']) && $KSGOperOne['Diag_id'] == $KSGOper['Diag_id'] && !empty($KSGOperOne['WithDuration']) && !empty($KSGOper['WithDuration']) && $KSGOperOne['ItogCoeff'] > $KSGOper['ItogCoeff']) // если диагнозы совпадают и длительность лечения учитывается в обоих, берём с наибольшим коэфф
								|| (!empty($KSGOperOne['Diag_id']) && $KSGOperOne['Diag_id'] == $KSGOper['Diag_id'] && empty($KSGOperOne['WithDuration']) && empty($KSGOper['WithDuration']) && $KSGOperOne['ItogCoeff'] > $KSGOper['ItogCoeff']) // если диагнозы совпадают и длительность лечения не учитывается в обоих, берём с наибольшим коэфф
							) {
								$KSGOper = $KSGOperOne;
							}
						} else {
							if (empty($KSGOper) || $KSGOperOne['ItogCoeff'] > $KSGOper['ItogCoeff'] || in_array($KSGOperOne['Mes_id'], $MesReabilIds)) {
								$KSGOper = $KSGOperOne;
							}
						}
					}
				}
			}
		}

		// Если для отделения МО указан объём «Отделение с определением КСГ по услуге», т.е. если код отделения равен значению атрибута «код отделения» объёма, для таких случаев лечения данного отделения КСГ определяется по услуге.
		$ignoreDiagKsg = false;
		if (getRegionNick() == 'buryatiya' && !empty($data['LpuSection_id'])) {
			$resp_vol = $this->queryResult("
				with mv as (select VolumeType_id from v_VolumeType where VolumeType_Code = '1' limit 1) -- Отделение с определением КСГ по услуге
				
				SELECT
					av.AttributeValue_id as \"AttributeValue_id\"
				FROM
					v_AttributeVision avis
					inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
					inner join v_LpuSection ls on ls.Lpu_id = :Lpu_id and ls.LpuSection_Code = av.AttributeValue_ValueString
					inner join lateral(
						select
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2
							inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.Lpu'
							and coalesce(av2.AttributeValue_ValueIdent, :Lpu_id) = :Lpu_id
						limit 1
					) MOFILTER on true
				WHERE
					avis.AttributeVision_TableName = 'dbo.VolumeType'
					and ls.LpuSection_id = :LpuSection_id
					and avis.AttributeVision_TablePKey = (select VolumeType_id from mv)
					and avis.AttributeVision_IsKeyValue = 2
					and coalesce(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
					and coalesce(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate	
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
		if (
			$needKSG && (
				$isAlgo2020 // для 2020 года грузим все КСГ (по диагнозу и услуге 1 запросом)
				|| (
					(empty($KSGOper) || !$ignoreDiagKsg)
					&& !empty($data['Diag_id'])
				)
			)
			&& !$KSGFromPolyTrauma
		) {
			$diagNidFilter = "and mu.Diag_nid IS NULL";
			if (!empty($DiagNids)) {
				$diagNidFilter = "and (mu.Diag_nid IS NULL OR mu.Diag_nid IN ('" . implode("','", $DiagNids) . "'))";
			}
			$diagOidFilter = "and mu.Diag_oid IS NULL";
			if (!empty($DiagOids)) {
				$diagOidFilter = "and (mu.Diag_oid IS NULL OR mu.Diag_oid IN ('" . implode("','", $DiagOids) . "'))";
			}

			$uslugaComplexFilter = "and mu.UslugaComplex_id is null";
			if (!empty($UslugaComplexIds)) {
				$uslugaComplexFilter = "and (mu.UslugaComplex_id is null OR mu.UslugaComplex_id IN ('" . implode("','", $UslugaComplexIds) . "'))";
			}

			$limit = "limit 1";
			if ($isAlgo2020 || getRegionNick() == 'perm') {
				$limit = "limit 100";
			}

			if ($isAlgo2020) {
				if (!empty($data['Diag_id'])) {
					$diagFilter = 'COALESCE(mu.Diag_id, CAST(:Diag_id as bigint)) = :Diag_id'; // 'ISNULL(mu.Diag_id, :Diag_id) = :Diag_id';
				} else {
					$diagFilter = 'mu.Diag_id is null';
				}
				$orderBySection = "";
			} else {
				$diagFilter = 'mu.Diag_id = :Diag_id';
				$orderBySection = "
						order by
							" . (!empty($MesReabilIds) ? "case when mo.Mes_id IN ('" . implode("','", $MesReabilIds) . "') then 1 else 0 end desc," : "") . "
							{$orderBy}
							\"ItogCoeff\" desc
					";
			}

			$query = "
				{$fractTempTable}
				
				select
					d.Diag_Code as \"Diag_Code\",
					mu.MesOldUslugaComplex_id as \"MesOldUslugaComplex_id\",
					mu.UslugaComplex_id as \"UslugaComplex_id\",
					mo.Mes_Code as \"Mes_Code\",
					mo.Mes_Name as \"Mes_Name\",
					mo.MesOld_Num as \"MesOld_Num\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"MesTariff_Value\",
					coalesce(moc.MesOldCoeff_Coeff, 1) * mt.MesTariff_Value as \"ItogCoeff\",
					mt.MesTariff_id as \"MesTariff_id\",
					mo.MesType_id as \"MesType_id\",
					mu.Diag_nid as \"Diag_nid\",
					mu.Diag_oid as \"Diag_oid\"
					{$kpgfields}
					{$dopFields}
				from v_MesOldUslugaComplex mu
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					{$FractionJoin}
					left join v_Diag d on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					left join dbo.v_MesOldCoeff moc on moc.Mes_id = mo.Mes_id -- Управленческий коэффициент
					{$kpgjoin}
				where
					{$diagFilter}
					{$uslugaComplexFilter}
					{$diagNidFilter}
					{$diagOidFilter}
					and (mu.Sex_id = :Sex_id OR Sex_id IS NULL)
					and (
						(:Person_Age >= 18 and mu.MesAgeGroup_id = 1)
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 2)
						or (:Person_AgeDays > 28 and mu.MesAgeGroup_id = 3)
						or (:Person_AgeDays <= 28 and mu.MesAgeGroup_id = 4) -- 1 от 0 до 28 дней включительно
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 5) -- 4 от 0 дней до 18 лет НЕ включительно
						or (:Person_Age >= 18 and mu.MesAgeGroup_id = 6) -- 5 18 лет и старше
						or (:Person_Age < 8 and mu.MesAgeGroup_id = 7)
						or (:Person_Age >= 8 and mu.MesAgeGroup_id = 8)
						or (:Person_AgeDays >= 29 and :Person_AgeDays <= 90 and mu.MesAgeGroup_id = 9) -- 2 от 29 до 90 дней включительно
						or (:Person_AgeDays >= 91 and :Person_Age < 1 and mu.MesAgeGroup_id = 10) -- 3 от 91 дня до 1 года НЕ включительно
						{$MesAgeGroup11Filter}
						or (mu.MesAgeGroup_id IS NULL)
					)
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and (mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL)
					{$DrugTherapySchemeFilter}
					{$MesDopFilter}
					and (mu.RehabScale_id = :RehabScale_id OR mu.RehabScale_id IS NULL)
					{$UslugaComplexFilters}
					and (mu.MesOldUslugaComplex_SofaScalePoints <= :EvnSection_SofaScalePoints OR mu.MesOldUslugaComplex_SofaScalePoints IS NULL)
					and (mu.MesOldUslugaComplex_IVLHours <= :EvnUsluga_IVLHours OR mu.MesOldUslugaComplex_IVLHours IS NULL)
					{$FractionFilter}
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (coalesce(mu.MesOldUslugaComplex_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					{$mesTypeFilter}
					and coalesce(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
					{$LpuSectionProfileFilter}
					{$LpuLevelFilter}
				{$orderBySection}
					{$limit}
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					if ($isAlgo2020) {
						$KSGList = $resp;
					} else {
						if (getRegionNick() == 'perm' || !empty($data['getFullArray'])) {
							foreach ($resp as $one_ksg) {
								$one_ksg['Mes_tid'] = $one_ksg['Mes_id'];
								$one_ksg['Mes_sid'] = null;
								$KSGFullArray[] = $one_ksg;
							}
						}

						$KSGTerr = $resp[0];
						// если определилась КСГ 4, то проверяем наличие услуг
						if (getRegionNick() == 'perm' && $KSGTerr['Mes_Code'] == '4') {
							if (
								!in_array('B01.001.006', $UslugaComplexCodes)
								&& !in_array('B01.001.009', $UslugaComplexCodes)
								&& !in_array('B02.001.002', $UslugaComplexCodes)
							) {
								$KSGTerr = false;
							}
						}
					}
				}
			}
		}

		// 3. Пробуем определить КПГ по профилю отделения
		if (
			(in_array(getRegionNick(), array('astra', 'buryatiya', 'kareliya', 'krym', 'vologda')))
			|| (getRegionNick() == 'ufa' && !$KSGOper && !$KSGTerr && !$KSGFromPolyTrauma)
		) {
			if (getRegionNick() == 'ufa' && !empty($data['EvnSection_id'])) {
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
						left join lateral(
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

			if (!empty($data['LpuSectionProfile_id'])) {
				$filter = (in_array(getRegionNick(), ['vologda'])) ? "(mo.LpuSectionProfile_id = :LpuSectionProfile_id or mo.LpuSectionProfile_id is null) " :
					"mo.LpuSectionProfile_id = :LpuSectionProfile_id ";
				$query = "
					select
						mo.Mes_Code as \"Mes_Code\",
						mo.Mes_Name as \"Mes_Name\",
						mo.MesOld_Num as \"MesOld_Num\",
						mo.Mes_id as \"Mes_id\",
						mtkpg.MesTariff_Value as \"MesTariff_Value\",
						mtkpg.MesTariff_Value as \"ItogCoeff\",
						mtkpg.MesTariff_id as \"MesTariff_id\",
						mo.MesType_id as \"MesType_id\"
					from MesOld mo
						inner join v_MesTariff mtkpg on mtkpg.Mes_id = mo.Mes_id -- Коэффициент КПГ
					where
						{$filter} and mo.MesType_id = 4 -- КПГ
						and mo.Mes_begDT <= :EvnSection_disDate
						and (coalesce(mo.Mes_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
						and mtkpg.MesTariff_begDT <= :EvnSection_disDate
						and (coalesce(mtkpg.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and coalesce(mtkpg.MesPayType_id, :MesPayType_id) = :MesPayType_id
					order by
						mo.LpuSectionProfile_id DESC,
						\"ItogCoeff\" desc
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

		if ($isAlgo2020 && !$KSGFromPolyTrauma) {
			// Алгоритм 2020 года refs #189829
			if (getRegionNick() == 'kareliya') {
				// refs #192457
				// Случаи лечения ЗНО, выявленного в результате неонкологического вмешательства
				if (
					!empty($data['EvnSection_id'])
					&& !empty($data['Diag_id'])
					&& !empty($data['LpuSectionProfile_id'])
					&& !empty($data['LpuSectionBedProfile_id'])
				) {
					$resp_check = $this->queryResult("
						select
							es.EvnSection_id as \"EvnSection_id\"
						from
							v_EvnSection es
							left join v_Diag d on d.Diag_id = :Diag_id
							left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = :LpuSectionProfile_id
							left join v_LpuSectionBedProfile lsbp on lsbp.LpuSectionBedProfile_id = :LpuSectionBedProfile_id
						where
							es.EvnSection_id = :EvnSection_id
							and lsp.LpuSectionProfile_Code not in ('18', '60')
							and lsbp.LpuSectionBedProfile_Code not in ('35', '36', '202', '203', '204', '205', '206', '207', '208')
							and (
								(d.Diag_Code >= 'C00' and d.Diag_Code <= 'C97')
								or (d.Diag_Code >= 'D00' and d.Diag_Code <= 'D09')
								or (d.Diag_Code = 'D70' and exists(
									select
										edps.EvnDiagPS_id
									from
										v_EvnDiagPS edps
										inner join v_Diag d2 on d2.Diag_id = edps.Diag_id
									where
										edps.EvnDiagPS_pid = es.EvnSection_id
										and edps.DiagSetClass_id <> 6
										and (
											(d2.Diag_Code >= 'C00' and d2.Diag_Code <= 'C80')
											OR d2.Diag_Code = 'C97'
										)
									limit 1
								))
							)
							and exists(
								select
									edps.EvnDiagPS_id
								from
									v_EvnDiagPS edps
									inner join v_Diag d2 on d2.Diag_id = edps.Diag_id
								where
									edps.EvnDiagPS_pid = es.EvnSection_id
									and edps.DiagSetClass_id = 6
									and (d2.Diag_Code < 'C00' or d2.Diag_Code > 'C97')
									and (d2.Diag_Code < 'D00' or d2.Diag_Code > 'D09')
									limit 1
							)
						limit 1
					", [
						'EvnSection_id' => $data['EvnSection_id'],
						'Diag_id' => $data['Diag_id'],
						'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
						'LpuSectionBedProfile_id' => $data['LpuSectionBedProfile_id']
					]);

					if (!empty($resp_check[0]['EvnSection_id'])) {
						// Оставляем только те связки, в которых нет диагноза, если такие есть
						$hasNotDiag = false;
						foreach ($KSGList as $oneKSG) {
							if (empty($oneKSG['Diag_id'])) {
								$hasNotDiag = true;
								break;
							}
						}
						if ($hasNotDiag) {
							foreach ($KSGList as $key => $oneKSG) {
								if (!empty($oneKSG['Diag_id'])) {
									unset($KSGList[$key]);
								}
							}
						}
					}
				}
			}

			// 2. Оставляем только те, в которых есть доп. критерий (шкала реабилитации, rbs, диагноз и <28 дней), если такие есть
			$hasDopCriteria = false;
			foreach($KSGList as $oneKSG) {
				if (!empty($oneKSG['hasDopCriteria']) || in_array($oneKSG['Mes_id'], $MesReabilIds)) {
					$hasDopCriteria = true;
					break;
				}
			}
			if ($hasDopCriteria) {
				foreach($KSGList as $key => $oneKSG) {
					if (empty($oneKSG['hasDopCriteria']) && !in_array($oneKSG['Mes_id'], $MesReabilIds)) {
						unset($KSGList[$key]);
					}
				}
			}

			if (getRegionNick() == 'perm' || !empty($data['getFullArray'])) {
				$KSGFullArray = $KSGList;
			}

			// 3. Проверяем по полученному списку КСГ связки в MesLink и исключаем те, у которых они есть
			$Mes_ids = [];
			foreach($KSGList as $oneKSG) {
				if (!in_array($oneKSG['Mes_id'], $Mes_ids)) {
					$Mes_ids[] = $oneKSG['Mes_id'];
				}
			}
			
			if (!empty($Mes_ids)) {
				$resp_ml = $this->queryResult("
					select
						Mes_id as \"Mes_id\"
					from
						v_MesLink
					where
						Mes_id IN ('".implode("','", $Mes_ids)."') and
						Mes_sid IN ('".implode("','", $Mes_ids)."') and
						MesLink_begDT <= :EvnSection_disDate and
						coalesce(MesLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate and
						MesLinkType_id = 2
				", array(
					'EvnSection_disDate' => $data['EvnSection_disDate']
				));
				foreach($resp_ml as $one_ml) {
					foreach($KSGList as $key => $oneKSG) {
						if ($oneKSG['Mes_id'] == $one_ml['Mes_id']) {
							unset($KSGList[$key]); // терапевтическая КСГ удаляется из перечня
						}
					}
				}
			}
                        
			// 4. если есть КСГ определенная по услуге + диагнозу и есть КСГ определенная только по услуге или только по диагнозу, то исключаем КСГ определенную только по услуге или только по диагнозу
			if (getRegionNick() != 'penza') { // refs PROMEDWEB-7608, "Чухланцева Ксения Сергеевна: у Пензы есть выбор КСГ из списка, поэтому им можно так сделать, если просят"
				$toExclude = [];
				foreach ($KSGList as $oneKSG) {
					if (!empty($oneKSG['UslugaComplex_id']) && !empty($oneKSG['Diag_id'])) {
						$toExclude[] = $oneKSG;
					}
				}
				foreach ($toExclude as $excKSG) {
					foreach ($KSGList as $key => $oneKSG) {
						if (
							(
								(!empty($oneKSG['UslugaComplex_id']) && empty($oneKSG['Diag_id']))
								|| (empty($oneKSG['UslugaComplex_id']) && !empty($oneKSG['Diag_id']))
							)
							// при условии что следующие критерии совпадают
							&& $oneKSG['Diag_oid'] == $excKSG['Diag_oid']
							&& $oneKSG['MesAgeGroup_id'] == $excKSG['MesAgeGroup_id']
							&& $oneKSG['Sex_id'] == $excKSG['Sex_id']
							&& $oneKSG['MesOldUslugaComplex_Duration'] == $excKSG['MesOldUslugaComplex_Duration']
							&& $oneKSG['MesOldUslugaComplex_DurationTo'] == $excKSG['MesOldUslugaComplex_DurationTo']
							&& $oneKSG['DrugTherapyScheme_id'] == $excKSG['DrugTherapyScheme_id']
							&& $oneKSG['MesOldUslugaComplex_SofaScalePoints'] == $excKSG['MesOldUslugaComplex_SofaScalePoints']
							&& $oneKSG['MesOldUslugaComplex_IVLHours'] == $excKSG['MesOldUslugaComplex_IVLHours']
							&& $oneKSG['MesDop_id'] == $excKSG['MesDop_id']
						) {
							unset($KSGList[$key]);
						}
					}
				}
			}

			// 5. если есть КСГ определенная по услуге + диагнозу + длительности и есть КСГ определенная только по услуге и диагнозу, то исключаем КСГ определенную только по услуге и диагнозу
			$toExclude = [];
			foreach ($KSGList as $oneKSG) {
				if (!empty($oneKSG['UslugaComplex_id']) && !empty($oneKSG['Diag_id']) && (!empty($oneKSG['MesOldUslugaComplex_Duration']) || !empty($oneKSG['MesOldUslugaComplex_DurationTo']))) {
					$toExclude[] = $oneKSG['UslugaComplex_id'] . '_' . $oneKSG['Diag_id'];
				}
			}
			foreach ($KSGList as $key => $oneKSG) {
				if (!empty($oneKSG['UslugaComplex_id']) && !empty($oneKSG['Diag_id']) && in_array($oneKSG['UslugaComplex_id'] . '_' . $oneKSG['Diag_id'], $toExclude) && empty($oneKSG['MesOldUslugaComplex_Duration']) && empty($oneKSG['MesOldUslugaComplex_DurationTo'])) {
					unset($KSGList[$key]);
				}
			}

			// 6. если есть КСГ определенная по услуге + фракциям + схеме и есть КСГ определенная только по услуге и фракциям, то исключаем КСГ определенную только по услуге и фракциям
			$toExclude = [];
			foreach ($KSGList as $oneKSG) {
				if (!empty($oneKSG['UslugaComplex_id']) && (!empty($oneKSG['MesOldUslugaComplex_FracFrom']) || !empty($oneKSG['MesOldUslugaComplex_FracTo'])) && !empty($oneKSG['DrugTherapyScheme_id'])) {
					$toExclude[] = $oneKSG['UslugaComplex_id'] . '_' . $oneKSG['MesOldUslugaComplex_FracFrom'] . '_' . $oneKSG['MesOldUslugaComplex_FracTo'];
				}
			}
			foreach ($KSGList as $key => $oneKSG) {
				if (!empty($oneKSG['UslugaComplex_id']) && (!empty($oneKSG['MesOldUslugaComplex_FracFrom']) || !empty($oneKSG['MesOldUslugaComplex_FracTo'])) && in_array($oneKSG['UslugaComplex_id'] . '_' . $oneKSG['MesOldUslugaComplex_FracFrom'] . '_' . $oneKSG['MesOldUslugaComplex_FracTo'], $toExclude) && empty($oneKSG['DrugTherapyScheme_id'])) {
					unset($KSGList[$key]);
				}
			}

			// 7. Берем из оставшихся КСГ ту у которой максимальный КЗ * упр. коэф
			$maxKSG = null;
			foreach($KSGList as $oneKSG) {
				if (
					empty($maxKSG)
					|| $maxKSG['ItogCoeff'] < $oneKSG['ItogCoeff']
					|| ($maxKSG['ItogCoeff'] == $oneKSG['ItogCoeff'] && empty($maxKSG['Mes_sid']) && !empty($oneKSG['Mes_sid'])) // если коэффициенты равны, то берем в первую очередь хирургическую КСГ
				) {
					$maxKSG = $oneKSG;
				}
			}

			if (getRegionNick() == 'krym') {
				// Для случаев с ДКЛ больше или равной 01.01.2020. Случай st02.004
				$found_key = array_search('st02.004', array_column($KSGList, 'MesOld_Num'), true);
				if ($found_key !== false) {
					$maxKSG = $KSGList[$found_key];
				}
			}

			if (!empty($maxKSG)) {
				if (!empty($maxKSG['Mes_tid'])) {
					$KSGTerr = $maxKSG;
				} else {
					$KSGOper = $maxKSG;
				}
			}
		}

		switch(true) {
			case getRegionNick() == 'astra':
			case getRegionNick() == 'buryatiya':
			case getRegionNick() == 'penza':
			case $isAlgo2020 && getRegionNick() == 'vologda':
				if (empty($KSGFromPolyTrauma)) {
					foreach($KSGList as $oneKSG) {
						if (!isset($combovalues[$oneKSG['Mes_id']]) || $combovalues[$oneKSG['Mes_id']]['ItogCoeff'] < $oneKSG['ItogCoeff']) {
							$combovalues[$oneKSG['Mes_id']] = array(
								'MesOldUslugaComplex_id' => $oneKSG['MesOldUslugaComplex_id'],
								'Mes_Code' => $oneKSG['Mes_Code'],
								'Mes_Name' => $oneKSG['Mes_Name'],
								'MesOld_Num' => $oneKSG['MesOld_Num'],
								'Mes_tid' => $oneKSG['Mes_tid'],
								'Mes_sid' => $oneKSG['Mes_sid'],
								'MesTariff_Value' => $oneKSG['MesTariff_Value'],
								'ItogCoeff' => $oneKSG['ItogCoeff'],
								'MesTariff_id' => $oneKSG['MesTariff_id'],
								'Mes_id' => $oneKSG['Mes_id'],
								'KPG' => $oneKSG['KPG'],
								'Mes_kid' => $oneKSG['Mes_kid'],
								'MesType_id' => $oneKSG['MesType_id'],
								'Mes_IsDefault' => (!empty($maxKSG) && $maxKSG['Mes_id'] == $oneKSG['Mes_id']) ? 2 : 1
							);
						}
					}
					$combovalues = array_values($combovalues);
				} else {
					$combovalues[] = array(
						'Mes_Code' => $KSGFromPolyTrauma['Mes_Code'],
						'Mes_Name' => $KSGFromPolyTrauma['Mes_Name'],
						'MesOld_Num' => $KSGFromPolyTrauma['MesOld_Num'],
						'Mes_tid' => $KSGFromPolyTrauma['Mes_id'],
						'Mes_sid' => null,
						'MesTariff_Value' => $KSGFromPolyTrauma['MesTariff_Value'],
						'MesTariff_id' => $KSGFromPolyTrauma['MesTariff_id'],
						'Mes_id' => $KSGFromPolyTrauma['Mes_id'],
						'KPG' => $KSGFromPolyTrauma['KPG'],
						'Mes_kid' => $KSGFromPolyTrauma['Mes_kid'],
						'MesType_id' => $KSGFromPolyTrauma['MesType_id'],
						'Mes_IsDefault' => 2
					);
				}

				if (in_array(getRegionNick(), ['vologda']) && $KPGFromLpuSectionProfile) {
					if (empty($combovalues)) {
						// если КСГ не определилась, то КПГ всё равно нужна
						$combovalues[] = [
							'Mes_Code' => null,
							'Mes_Name' => null,
							'MesOld_Num' => null,
							'Mes_tid' => null,
							'Mes_sid' => null,
							'MesTariff_Value' => null,
							'MesTariff_id' => null,
							'Mes_id' => null,
							'KPG' => null,
							'Mes_kid' => null,
							'MesType_id' => null,
							'Mes_IsDefault' => 2
						];
					}

					foreach($combovalues as $key => $value) {
						$combovalues[$key]['KPG'] = $KPGFromLpuSectionProfile['Mes_Code'] . '. ' . $KPGFromLpuSectionProfile['MesOld_Num'] . '. ' . $KPGFromLpuSectionProfile['Mes_Name'];
						$combovalues[$key]['Mes_kid'] = $KPGFromLpuSectionProfile['Mes_id'];
						$combovalues[$key]['MesTariff_sid'] = $KPGFromLpuSectionProfile['MesTariff_id'];
						$combovalues[$key]['MesTariff_sValue'] = $KPGFromLpuSectionProfile['MesTariff_Value'];
					}
				}

				return $combovalues;
				break;

			default:
				$response = array('Mes_Code' => '', 'Mes_Name' => '', 'MesOld_Num' => '', 'MesTariff_Value' => '', 'MesTariff_sValue' => '', 'KSGArray' => array(), 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'MesTariff_sid' => null, 'MesOldUslugaComplex_id' => null, 'EvnSection_TotalFract' => null, 'EvnSection_CoeffCTP' => '');

				if ($KPGFromLpuSectionProfile) {
					$response['KPG'] = $KPGFromLpuSectionProfile['Mes_Code'] . '. ' . $KPGFromLpuSectionProfile['MesOld_Num'] . '. ' . $KPGFromLpuSectionProfile['Mes_Name'];
					$response['Mes_kid'] = $KPGFromLpuSectionProfile['Mes_id'];
					$response['MesTariff_sid'] = $KPGFromLpuSectionProfile['MesTariff_id'];
					$response['MesTariff_sValue'] = $KPGFromLpuSectionProfile['MesTariff_Value'];
				}

				if ($KSGOper && $KSGTerr) {
					// если обе определились, то ищем связь в MesLink, если есть то берём хирургическую!
					// для Перми: Если КСГ 5 «Кесарево сечение» определилась по услуге, то отнесение группы движений осуществляется к КСГ 5. Независимо от того, какая КСГ определилась по коду диагноза и иных кодов услуг.
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

					if (in_array(getRegionNick(), array('ufa', 'vologda'))) {
						if (in_array($KSGOper['MesType_id'], array(9, 10))) {
							$response['Mes_tid'] = $KSGOper['Mes_id'];
						} else {
							$response['Mes_sid'] = $KSGOper['Mes_id'];
						}
					} else {
						$response['Mes_sid'] = $KSGOper['Mes_id'];
					}

					if ((
							$KSGOper['ItogCoeff'] >= $KSGTerr['ItogCoeff']
							|| !empty($data['MesLink_id'])
							|| in_array($KSGOper['Mes_id'], $MesReabilIds)
							|| (getRegionNick() == 'perm' && $KSGOper['Mes_Code'] == 5)
							|| getRegionNick() == 'pskov'
							|| getRegionNick() == 'vologda'
						) && (
							!in_array(getRegionNick(), array('pskov', 'vologda')) || $KSGOper['criteriaCount'] >= $KSGTerr['criteriaCount']
						)) {
						if (!empty($response['Mes_sid']) && !empty($data['MesLink_id'])) {
							$response['Mes_tid'] = null;
						}
						$response['Mes_Code'] = $KSGOper['Mes_Code'];
						$response['Mes_Name'] = $KSGOper['Mes_Name'];
						$response['MesOld_Num'] = $KSGOper['MesOld_Num'];
						$response['MesTariff_Value'] = $KSGOper['MesTariff_Value'];
						$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
						$response['MesOldUslugaComplex_id'] = $KSGOper['MesOldUslugaComplex_id'];
						$response['EvnSection_TotalFract'] = $KSGOper['EvnSection_TotalFract'];
						$response['Mes_id'] = $KSGOper['Mes_id'];
						$response['KPG'] = $KSGOper['KPG'];
						$response['Mes_kid'] = !empty($KSGOper['Mes_kid']) ? $KSGOper['Mes_kid'] : $response['Mes_kid'];
					} else {
						$response['Mes_Code'] = $KSGTerr['Mes_Code'];
						$response['Mes_Name'] = $KSGTerr['Mes_Name'];
						$response['MesOld_Num'] = $KSGTerr['MesOld_Num'];
						$response['MesTariff_Value'] = $KSGTerr['MesTariff_Value'];
						$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
						$response['MesOldUslugaComplex_id'] = $KSGTerr['MesOldUslugaComplex_id'];
						$response['EvnSection_TotalFract'] = $KSGTerr['EvnSection_TotalFract'];
						$response['Mes_id'] = $KSGTerr['Mes_id'];
						$response['KPG'] = $KSGTerr['KPG'];
						$response['Mes_kid'] = !empty($KSGTerr['Mes_kid']) ? $KSGTerr['Mes_kid'] : $response['Mes_kid'];
					}
				} else if ($KSGOper) {
					$response['Mes_Code'] = $KSGOper['Mes_Code'];
					$response['Mes_Name'] = $KSGOper['Mes_Name'];
					$response['MesOld_Num'] = $KSGOper['MesOld_Num'];
					if (in_array(getRegionNick(), array('ufa', 'vologda'))) {
						if (in_array($KSGOper['MesType_id'], array(9, 10))) {
							$response['Mes_tid'] = $KSGOper['Mes_id'];
						} else {
							$response['Mes_sid'] = $KSGOper['Mes_id'];
						}
					} else {
						$response['Mes_sid'] = $KSGOper['Mes_id'];
					}
					$response['MesTariff_Value'] = $KSGOper['MesTariff_Value'];
					$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
					$response['MesOldUslugaComplex_id'] = $KSGOper['MesOldUslugaComplex_id'];
					$response['EvnSection_TotalFract'] = $KSGOper['EvnSection_TotalFract'];
					$response['Mes_id'] = $KSGOper['Mes_id'];
					$response['KPG'] = $KSGOper['KPG'];
					$response['Mes_kid'] = !empty($KSGOper['Mes_kid']) ? $KSGOper['Mes_kid'] : $response['Mes_kid'];
				} else if ($KSGTerr) {
					$response['Alert_Code'] = 1; // 'Не найдено КСГ по операциям';
					$response['Mes_Code'] = $KSGTerr['Mes_Code'];
					$response['Mes_Name'] = $KSGTerr['Mes_Name'];
					$response['MesOld_Num'] = $KSGTerr['MesOld_Num'];
					$response['Mes_tid'] = $KSGTerr['Mes_id'];
					$response['MesTariff_Value'] = $KSGTerr['MesTariff_Value'];
					$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
					$response['MesOldUslugaComplex_id'] = $KSGTerr['MesOldUslugaComplex_id'];
					$response['EvnSection_TotalFract'] = $KSGTerr['EvnSection_TotalFract'];
					$response['Mes_id'] = $KSGTerr['Mes_id'];
					$response['KPG'] = $KSGTerr['KPG'];
					$response['Mes_kid'] = !empty($KSGTerr['Mes_kid']) ? $KSGTerr['Mes_kid'] : $response['Mes_kid'];
				} else if ($KPGFromLpuSectionProfile) {
					$response['Alert_Code'] = 2; // 'Не найдено КСГ по операциям и диагнозу';
					$response['MesTariff_Value'] = $KPGFromLpuSectionProfile['MesTariff_Value'];
					$response['MesTariff_id'] = $KPGFromLpuSectionProfile['MesTariff_id'];
					$response['Mes_kid'] = !empty($KPGFromLpuSectionProfile['Mes_id']) ? $KPGFromLpuSectionProfile['Mes_id'] : $response['Mes_kid'];
				}

				if ($KSGFromPolyTrauma) {
					$response['Mes_Code'] = $KSGFromPolyTrauma['Mes_Code'];
					$response['Mes_Name'] = $KSGFromPolyTrauma['Mes_Name'];
					$response['MesOld_Num'] = $KSGFromPolyTrauma['MesOld_Num'];
					$response['Mes_tid'] = $KSGFromPolyTrauma['Mes_id'];
					$response['MesTariff_Value'] = $KSGFromPolyTrauma['MesTariff_Value'];
					$response['MesTariff_id'] = $KSGFromPolyTrauma['MesTariff_id'];
					$response['Mes_id'] = $KSGFromPolyTrauma['Mes_id'];
					$response['KPG'] = $KSGFromPolyTrauma['KPG'];
					$response['Mes_kid'] = !empty($KSGFromPolyTrauma['Mes_kid']) ? $KSGFromPolyTrauma['Mes_kid'] : $response['Mes_kid'];
				}

				if ( !empty($EvnSection_TotalFract) && empty($response['EvnSection_TotalFract']) ) {
					$response['EvnSection_TotalFract'] = $EvnSection_TotalFract;
				}
				if (in_array(getRegionNick(), array('khak', 'pskov'))) {
					$response['UslugaComplex_id'] = null;
					$response['MesOldUslugaComplexLink_Number'] = null;

					if (!empty($response['MesOld_Num']) && $response['MesOld_Num'] == 'st29.007') {
						// для политравмы нет связок в группировщике
						$resp_moucl = $this->queryResult("
							select
								UslugaComplex_id as \"UslugaComplex_id\"
							from
								v_UslugaComplex
							where
								UslugaComplex_Code = '066078'
								and UslugaComplex_begDT <= :EvnSection_disDate
								and coalesce(UslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate
							limit 1
						", array(
							'EvnSection_disDate' => $data['EvnSection_disDate']
						));

						if (!empty($resp_moucl[0])) {
							$response['UslugaComplex_id'] = $resp_moucl[0]['UslugaComplex_id'];
						}

						if (getRegionNick() == 'pskov') {
							$response['MesOldUslugaComplexLink_Number'] = '35934';
						}
					} else if (!empty($response['MesOldUslugaComplex_id'])) {
						if (getRegionNick() == 'khak') {
							$resp_moucl = $this->queryResult("
								select
									UslugaComplex_id as \"UslugaComplex_id\"
								from
									v_MesOldUslugaComplex
								where
									MesOldUslugaComplex_id = :MesOldUslugaComplex_id
									and MesOldUslugaComplex_begDT <= :EvnSection_disDate
									and coalesce(MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate
								limit 1
							", array(
								'MesOldUslugaComplex_id' => $response['MesOldUslugaComplex_id'],
								'EvnSection_disDate' => $data['EvnSection_disDate']
							));

							if (!empty($resp_moucl[0])) {
								$response['UslugaComplex_id'] = $resp_moucl[0]['UslugaComplex_id'];
							}
						} else {
							$resp_moucl = $this->queryResult("
								select
									UslugaComplex_id as \"UslugaComplex_id\",
									MesOldUslugaComplexLink_Number as \"MesOldUslugaComplexLink_Number\"
								from
									r60.v_MesOldUslugaComplexLink
								where
									MesOldUslugaComplex_id = :MesOldUslugaComplex_id
									and MesOldUslugaComplexLink_begDT <= :EvnSection_disDate
									and coalesce(MesOldUslugaComplexLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate
								limit 1
							", array(
								'MesOldUslugaComplex_id' => $response['MesOldUslugaComplex_id'],
								'EvnSection_disDate' => $data['EvnSection_disDate']
							));

							if (!empty($resp_moucl[0])) {
								$response['UslugaComplex_id'] = $resp_moucl[0]['UslugaComplex_id'];
								$response['MesOldUslugaComplexLink_Number'] = $resp_moucl[0]['MesOldUslugaComplexLink_Number'];
							}
						}
					}
				}

				if (in_array(getRegionNick(), ['perm', 'vologda'])) {
					// КСГ одна, но на форме комбобокс
					if (!empty($response['Mes_id'])) {
						$response['Mes_IsDefault'] = 2;
						$combovalues[] = $response;
					}

					foreach ($combovalues as &$combovalue) {
						if (!empty($combovalue['Mes_tid']) && !in_array($combovalue['Mes_tid'], $combovalue['KSGArray'])) {
							$combovalue['KSGArray'][] = $combovalue['Mes_tid'];
						}
						if (!empty($combovalue['Mes_sid']) && !in_array($combovalue['Mes_sid'], $combovalue['KSGArray'])) {
							$combovalue['KSGArray'][] = $combovalue['Mes_sid'];
						}
						foreach ($KSGFullArray as $KSGOne) {
							if (!in_array($KSGOne['Mes_id'], $combovalue['KSGArray'])) {
								$combovalue['KSGArray'][] = $KSGOne['Mes_id'];
							}
						}

						if (!empty($combovalue['MesTariff_Value'])) {
							$combovalue['MesTariff_Value'] = round($combovalue['MesTariff_Value'], 3);
						}

						if (!empty($data['getFullArray'])) {
							$combovalue['KSGFullArray'] = $KSGFullArray;
						}
					}

					return $combovalues;
				} else {
					return $response;
				}
				break;
		}
	}
}
