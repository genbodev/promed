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
* @version			astra
*/

require_once(APPPATH.'models/EvnSection_model.php');

class Astra_EvnSection_model extends EvnSection_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * поиск ксг/кпг/коэф для 2020 года
	 */
	function loadKSGKPGKOEFCombo2020($data) {
		if (empty($data['EvnSectionIds'])) {
			$data['EvnSectionIds'] = array();
			if (!empty($data['EvnSection_id'])) {
				$data['EvnSectionIds'][] = $data['EvnSection_id'];
			}

			$groupped = $this->getEvnSectionGroup(array(
				'EvnSection_id' => $data['EvnSection_id'],
				'EvnSection_pid' => $data['EvnSection_pid'],
				'EvnSection_setDate' => $data['EvnSection_setDate'],
				'EvnSection_disDate' => $data['EvnSection_disDate'],
				'HTMedicalCareClass_id' => $data['HTMedicalCareClass_id'],
				'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
				'Diag_id' => $data['Diag_id'],
				'CureResult_id' => $data['CureResult_id'],
				'PayType_id' => $data['PayType_id'],
				'Person_id' => $data['Person_id']
			));


			foreach ($groupped['group2'] as $group) {
				if (in_array($data['EvnSection_id'], $group['EvnSectionIdsWithoutReanim'])) {
					$data['EvnSectionIds'] = $group['EvnSectionIdsWithoutReanim'];
				}
			}
		}

		// Используем общий алгоритм
		$this->load->model('MesOldUslugaComplex_model');
		return $this->MesOldUslugaComplex_model->getKSGKPGKOEFF($data);
	}

	/**
	 * поиск ксг/кпг/коэф для 2019 года
	 */
	function loadKSGKPGKOEFCombo2019($data) {
		// Используем общий алгоритм
		// $this->load->model('MesOldUslugaComplex_model');
		// return $this->MesOldUslugaComplex_model->getKSGKPGKOEFF($data);

		// Смена концепции
		// @task https://redmine.swan-it.ru/issues/155336
		$combovalues = array();
		$KSGList = array();
		$KSGFromPolyTrauma = false;

		if (empty($data['EvnSection_setDate'])) {
			$data['EvnSection_setDate'] = date('Y-m-d');
		}

		if (empty($data['EvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['EvnSection_setDate'];
		}

		if (substr($data['EvnSection_disDate'], 0, 4) >= '2020') {
			// алгоритм с 2020 года
			return $this->loadKSGKPGKOEFCombo2020($data);
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
				if (substr($data['LastEvnSection_disDate'], 0, 4) >= '2020') {
					return $this->loadKSGKPGKOEFCombo2020($data);
				}
			}
		}

		// считаем длительность пребывания
		$datediff = strtotime($data['EvnSection_disDate']) - strtotime($data['EvnSection_setDate']);
		$data['Duration'] = floor($datediff / (60 * 60 * 24));
		// для Астрахани: Расчёт длительности при определении КСГ для круглосуточного и дневного стационара выполняется следующим образом: Дата выписки – Дата поступления + 1 день.
		$data['Duration'] += 1; // для дневного +1

		// если движение из предыдущего года, то связки берём на дату последнего движения КВС
		if (!empty($data['LastEvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['LastEvnSection_disDate'];
		}

		$mesTypeFilter = " and mo.MesType_id IN (10,14)";
		$data['MesPayType_id'] = 10;
		if (in_array($data['LpuUnitType_id'], array('6', '7', '9'))) {
			$mesTypeFilter = " and mo.MesType_id IN (9,13)";
			$data['MesPayType_id'] = 9;
		}

		$filter_paytype = "and pt.PayType_SysNick = 'oms'";

		// получаем список сгруппированных движений, диагнозы и услуги будем брать из всех движений группы
		if (!empty($data['EvnSectionIds'])) {
			$EvnSectionIds = $data['EvnSectionIds'];
		} else {
			$EvnSectionIds = array();
			if (!empty($data['EvnSection_id'])) {
				$EvnSectionIds = array();
				$EvnSectionIds[] = $data['EvnSection_id'];
			}
		}

		$query = "
			select
				dbo.AgeTFOMS(PS.Person_BirthDay, :EvnSection_setDate) as Person_Age,
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
						uc.UslugaComplex_id,
						uc.UslugaComplex_Code
					from
						v_EvnUsluga eu (nolock)
						inner join v_PayType pt (nolock) on pt.PayType_id = eu.PayType_id {$filter_paytype}
						inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					where
						eu.EvnUsluga_pid IN ('" . implode("','", $EvnSectionIds) . "')
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

		$data['EvnUsluga_IVLHours'] = 0;
		if (!empty($EvnSectionIds)) {
			$query = "
				select
					eu.EvnUsluga_setDT,
					eu.EvnUsluga_disDT
				from
					v_EvnUsluga eu (nolock)
					inner join v_PayType pt (nolock) on pt.PayType_id = eu.PayType_id {$filter_paytype}
					inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
				where
					eu.EvnUsluga_pid IN ('" . implode("','", $EvnSectionIds) . "')
					and eu.EvnUsluga_setDT is not null
					and eu.EvnUsluga_disDT is not null
					and eu.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaPar', 'EvnUslugaStom')
					and exists(
						select top 1
							uca.UslugaComplexAttribute_id
						from
							v_UslugaComplexAttribute uca (nolock)
							inner join v_UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
						where
							uca.UslugaComplex_id = eu.UslugaComplex_id
							and ucat.UslugaComplexAttributeType_SysNick = 'ivl'
					)
			";
			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				foreach ($resp as $respone) {
					$data['EvnUsluga_IVLHours'] += $respone['EvnUsluga_disDT']->getTimestamp() - $respone['EvnUsluga_setDT']->getTimestamp();
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
			$SoputDiagIds = $data['SoputDiagIds'];
			$SoputDiagCodes = $data['SoputDiagCodes'];
		} else {
			$SoputDiagIds = array();
			$SoputDiagCodes = array();
			// получаем все сопутствующие диагнозы группы
			if (!empty($EvnSectionIds)) {
				$query = "
					select distinct
						d.Diag_id,
						d.Diag_Code
					from
						v_EvnDiagPS edps (nolock)
						inner join v_Diag d (nolock) on d.Diag_id = edps.Diag_id
					where
						edps.DiagSetClass_id IN (2,3)
						and	edps.EvnDiagPS_pid IN ('" . implode("','", $EvnSectionIds) . "')
				";
				$result = $this->db->query($query, $data);

				if (is_object($result)) {
					$resp = $result->result('array');
					foreach ($resp as $respone) {
						$SoputDiagIds[] = $respone['Diag_id'];
						$SoputDiagCodes[] = $respone['Diag_Code'];
					}
				}
			}
		}

		if (!in_array($data['LpuUnitType_id'], array('6', '7', '9'))) {
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
						select top 1
							pt.PolyTrauma_Code,
							SOPUT.Diag_id
						from
							v_PolyTrauma pt (nolock)
							outer apply(
								select top 1
									pt2.Diag_id
								from
									v_PolyTrauma pt2 (nolock)
								where
									pt2.Diag_id IN ('" . implode("','", $SoputDiagIds) . "')
									and pt2.PolyTrauma_Code <> pt.PolyTrauma_Code
									and pt2.PolyTrauma_begDT <= :EvnSection_disDate
									and (IsNull(pt2.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
							) SOPUT
						where
							pt.Diag_id IN ('" . implode("','", $SoputDiagIdsWithOsn) . "')
							and pt.PolyTrauma_begDT <= :EvnSection_disDate
							and (IsNull(pt.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						order by
							SOPUT.Diag_id desc,  -- в первую очередь с заполненным диагнозом
							case when pt.PolyTrauma_Code = 7 then 1 else 0 end desc -- в первую очередь с кодом 7
					";

					$result = $this->db->query($query, $data);

					if (is_object($result)) {
						$resp = $result->result('array');
						if (count($resp) > 0) {
							if ($resp[0]['PolyTrauma_Code'] == 7 || !empty($resp[0]['Diag_id'])) {
								// берём КСГ 248 (st29.007)
								$query = "
									select top 1
										mo.Mes_Code,
										mo.Mes_Name,
										mo.MesOld_Num,
										mo.Mes_id,
										mt.MesTariff_Value,
										mt.MesTariff_id,
										mo.MesType_id,
										mouc.MesOldUslugaComplex_id
									from
										v_MesOld mo (nolock)
										left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
										outer apply (
											select top 1 MesOldUslugaComplex_id
											from v_MesOldUslugaComplex with (nolock)
											where Mes_id = mo.Mes_id
												and MesOldUslugaComplex_begDT <= :EvnSection_disDate
												and (IsNull(MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
										) mouc
									where
										mo.Mes_Code = '248'
										{$mesTypeFilter}
										and mo.Mes_begDT <= :EvnSection_disDate
										and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
										and mt.MesTariff_begDT <= :EvnSection_disDate
										and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								";

								$result = $this->db->query($query, $data);
								if (is_object($result)) {
									$resp_ksg = $result->result('array');
									if (count($resp_ksg) > 0) {
										if (!empty($data['getFullArray'])) {
											foreach($resp_ksg as $one_ksg) {
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

		if (!$KSGFromPolyTrauma) {
			// Определение КСГ с учётом указанного кол-ва фракций проведения лучевой терапии
			$fractTempTable = "";
			if (!empty($EvnSectionIds) && !empty($UslugaComplexIds)) {
				$resp_fract = $this->queryResult("
					select
						UslugaComplex_id,
						SUM(ISNULL(CountFractionRT, 0)) as CountFractionRT
					from
						(
							select
								UslugaComplex_id,
								EvnUslugaOnkoBeam_CountFractionRT as CountFractionRT
							from
								v_EvnUslugaOnkoBeam euog (nolock)
							where
								euog.UslugaComplex_id IN ('" . implode("','", $UslugaComplexIds) . "')
								and	euog.EvnUslugaOnkoBeam_pid IN ('" . implode("','", $EvnSectionIds) . "')
							
							union all
								
							select
								UslugaComplex_id,
								EvnUslugaOnkoGormun_CountFractionRT as CountFractionRT
							from
								v_EvnUslugaOnkoGormun euog (nolock)
							where
								euog.UslugaComplex_id IN ('" . implode("','", $UslugaComplexIds) . "')
								and	euog.EvnUslugaOnkoGormun_pid IN ('" . implode("','", $EvnSectionIds) . "')
						) onkousl
						
					group by UslugaComplex_id
				");

				if (!empty($resp_fract)) {
					$fractTempTable = "
						declare @FRACTION table (
							UslugaComplex_id bigint,
							CountFractionRT bigint
						);
						
						set nocount on;
					";
					foreach ($resp_fract as $one_fract) {
						$fractTempTable .= "
							insert into @FRACTION (UslugaComplex_id, CountFractionRT) values ({$one_fract['UslugaComplex_id']}, {$one_fract['CountFractionRT']});				
						";
					}

					$fractTempTable .= "
						set nocount off;				
					";
				}
			}

			// 1. Пробуем определить КСГ по наличию диагноза
			if (!empty($data['Diag_id'])) {
				$soputDiagFilter = "and mu.Diag_nid IS NULL";
				if (!empty($SoputDiagIds)) {
					$soputDiagFilter = "and (mu.Diag_nid IS NULL OR mu.Diag_nid IN ('" . implode("','", $SoputDiagIds) . "'))";
				}

				$FractionJoin = "";
				$FractionFilter = "
					and mu.MesOldUslugaComplex_FracFrom IS NULL
					and mu.MesOldUslugaComplex_FracTo IS NULL
				";
				if (!empty($fractTempTable)) {
					$FractionJoin = "left join @FRACTION fr on fr.UslugaComplex_id = mu.UslugaComplex_id";
					$FractionFilter = "
						and (mu.MesOldUslugaComplex_FracFrom <= fr.CountFractionRT OR mu.MesOldUslugaComplex_FracFrom IS NULL)
						and (mu.MesOldUslugaComplex_FracTo >= fr.CountFractionRT OR mu.MesOldUslugaComplex_FracTo IS NULL)
					";
				}

				$query = "
					{$fractTempTable}
					
					select
						d.Diag_Code,
						mu.MesOldUslugaComplex_id,
						mo.Mes_Code,
						mo.Mes_Name,
						mo.MesOld_Num,
						mo.Mes_id,
						mt.MesTariff_Value,
						mt.MesTariff_id,
						mo.MesType_id,
						mu.Diag_nid,
						case when mu.DrugTherapyScheme_id is not null then 1 else 0 end
							+ case when mu.RehabScale_id is not null then 1 else 0 end
							+ case when mu.UslugaComplex_aid is not null or mu.UslugaComplex_bid is not null then 1 else 0 end -- считаются как 1 критерий
							+ case when mu.MesOldUslugaComplex_FracFrom is not null or mu.MesOldUslugaComplex_FracTo is not null then 1 else 0 end -- считаются как 1 критерий
							+ case when mu.MesOldUslugaComplex_SofaScalePoints is not null or mu.MesOldUslugaComplex_IVLHours is not null then 1 else 0 end
						as dopCriteriaCount
					from v_MesOldUslugaComplex mu (nolock)
						inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
						{$FractionJoin}
						left join v_Diag d (nolock) on d.Diag_id = mu.Diag_id
						left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					where
						mu.Diag_id = :Diag_id
						and mu.UslugaComplex_id is null
						{$soputDiagFilter}
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
						and (mu.RehabScale_id = :RehabScale_id OR mu.RehabScale_id IS NULL)
						{$UslugaComplexFilters}
						and (mu.MesOldUslugaComplex_SofaScalePoints <= :EvnSection_SofaScalePoints OR mu.MesOldUslugaComplex_SofaScalePoints IS NULL)
						and (mu.MesOldUslugaComplex_IVLHours <= :EvnUsluga_IVLHours OR mu.MesOldUslugaComplex_IVLHours IS NULL)
						{$FractionFilter}
						and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
						and (IsNull(mu.MesOldUslugaComplex_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
						and mo.Mes_begDT <= :EvnSection_disDate
						and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and mt.MesTariff_begDT <= :EvnSection_disDate
						and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						{$mesTypeFilter}
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
						+ case when mu.MesOldUslugaComplex_FracFrom is not null or mu.MesOldUslugaComplex_FracTo is not null then 1 else 0 end -- считаются как 1 критерий
						+ case when mu.MesOldUslugaComplex_SofaScalePoints is not null or mu.MesOldUslugaComplex_IVLHours is not null then 1 else 0 end desc, -- считаются как 1 критерий
						mt.MesTariff_Value desc
				";

				$result = $this->db->query($query, $data);

				if (is_object($result)) {
					$resp = $result->result('array');
					foreach ($resp as $respone) {
						if (!isset($KSGList[$respone['Mes_id']]) || $KSGList[$respone['Mes_id']]['MesTariff_Value'] < $respone['MesTariff_Value']) {
							$respone['isTerr'] = true;
							$KSGList[$respone['Mes_id']] = $respone;
						}
					}
				}
			}

			// 2. Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
			if (!empty($UslugaComplexIds)) {
				// 1 шаг: ищем КСГ по услуге без учета остальных критериев
				$step = 1;

				$query = "
					select
						uc.UslugaComplex_id,
						mu.MesOldUslugaComplex_id,
						mo.Mes_Code,
						mo.Mes_Name,
						mo.MesOld_Num,
						mo.Mes_id,
						mt.MesTariff_Value,
						mt.MesTariff_id,
						mo.MesType_id
					from
						v_UslugaComplex uc (nolock)
						inner join v_MesOldUslugaComplex mu (nolock) on mu.UslugaComplex_id = uc.UslugaComplex_id
						inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
						left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id
					where
						uc.UslugaComplex_id in ('" . implode("','", $UslugaComplexIds) . "')
						and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
						and (IsNull(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
						and mo.Mes_begDT <= :EvnSection_disDate
						and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and mt.MesTariff_begDT <= :EvnSection_disDate
						and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						{$mesTypeFilter}
						and ISNULL(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
					order by
						mt.MesTariff_Value desc
				";

				$result = $this->db->query($query, $data);

				if (is_object($result)) {
					$resp = $result->result('array');
					$KSGListByUsluga = array();
					$Mes_id = null;

					foreach ($resp as $respone) {
						$Mes_id = $respone['Mes_id'];

						if (!isset($KSGListByUsluga[$Mes_id]) || $KSGListByUsluga[$Mes_id]['MesTariff_Value'] < $respone['MesTariff_Value']) {
							$KSGListByUsluga[$Mes_id] = $respone;
						}
					}

					if ( count($KSGListByUsluga) == 1 ) {
						if (!isset($KSGList[$Mes_id]) || $KSGList[$Mes_id]['MesTariff_Value'] < $KSGListByUsluga[$Mes_id]['MesTariff_Value']) {
							$KSGList[$Mes_id] = $KSGListByUsluga[$Mes_id];
						}
					}
					else {
						$step = 2;
					}
				}
				else {
					$step = 2;
				}
				
				// 2 шаг: ище с учетом доп. критериев
				// выполняется, если на шаге 1 нашли более одной КСГ
				if ( $step == 2 ) {
					$soputDiagFilter = "and mu.Diag_nid IS NULL";
					if (!empty($SoputDiagIds)) {
						$soputDiagFilter = "and (mu.Diag_nid IS NULL OR mu.Diag_nid IN ('" . implode("','", $SoputDiagIds) . "'))";
					}

					$FractionJoin = "";
					$FractionFilter = "
						and mu.MesOldUslugaComplex_FracFrom IS NULL
						and mu.MesOldUslugaComplex_FracTo IS NULL
					";
					if (!empty($fractTempTable)) {
						$FractionJoin = "left join @FRACTION fr on fr.UslugaComplex_id = mu.UslugaComplex_id";
						$FractionFilter = "
							and (mu.MesOldUslugaComplex_FracFrom <= fr.CountFractionRT OR mu.MesOldUslugaComplex_FracFrom IS NULL)
							and (mu.MesOldUslugaComplex_FracTo >= fr.CountFractionRT OR mu.MesOldUslugaComplex_FracTo IS NULL)
						";
					}

					$query = "
						{$fractTempTable}
								
						select
							uc.UslugaComplex_id,
							mu.MesOldUslugaComplex_id,
							mo.Mes_Code,
							mo.Mes_Name,
							mo.MesOld_Num,
							mo.Mes_id,
							mt.MesTariff_Value,
							mt.MesTariff_id,
							mo.MesType_id,
							mu.Diag_nid,
							mu.Diag_id,
							case when mu.DrugTherapyScheme_id is not null then 1 else 0 end
								+ case when mu.RehabScale_id is not null then 1 else 0 end
								+ case when mu.MesOldUslugaComplex_FracFrom is not null or mu.MesOldUslugaComplex_FracTo is not null then 1 else 0 end -- считаются как 1 критерий
								+ case when mu.MesOldUslugaComplex_SofaScalePoints is not null or mu.MesOldUslugaComplex_IVLHours is not null then 1 else 0 end
							as dopCriteriaCount
						from
							v_UslugaComplex uc (nolock)
							inner join v_MesOldUslugaComplex mu (nolock) on mu.UslugaComplex_id = uc.UslugaComplex_id
							inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
							{$FractionJoin}
							left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
						where
							uc.UslugaComplex_id IN ('" . implode("','", $UslugaComplexIds) . "')
							and (
								(mu.Diag_id IS NULL) OR
								(mu.Diag_id = :Diag_id and mu.MesOldUslugaComplex_IsDiag = 2) OR
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
								)
							)
							{$soputDiagFilter}
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
							and (mu.RehabScale_id = :RehabScale_id OR mu.RehabScale_id IS NULL)
							{$UslugaComplexFilters}
							and (mu.MesOldUslugaComplex_SofaScalePoints <= :EvnSection_SofaScalePoints OR mu.MesOldUslugaComplex_SofaScalePoints IS NULL)
							and (mu.MesOldUslugaComplex_IVLHours <= :EvnUsluga_IVLHours OR mu.MesOldUslugaComplex_IVLHours IS NULL)
							{$FractionFilter}
							and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
							and (IsNull(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
							and mo.Mes_begDT <= :EvnSection_disDate
							and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
							and mt.MesTariff_begDT <= :EvnSection_disDate
							and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
							{$mesTypeFilter}
							and ISNULL(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
						order by
							uc.UslugaComplex_id,
							case when mu.Diag_id is not null and mu.MesOldUslugaComplex_IsDiag = 2 then 1 else 0 end desc, -- по диагнозу берём в первую очередь
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
							mt.MesTariff_Value desc
					";

					$result = $this->db->query($query, $data);

					if (is_object($result)) {
						$resp = $result->result('array');
						foreach ($resp as $respone) {
							if (!isset($KSGList[$respone['Mes_id']]) || $KSGList[$respone['Mes_id']]['MesTariff_Value'] < $respone['MesTariff_Value']) {
								$KSGList[$respone['Mes_id']] = $respone;
							}
						}
					}
				}
			}

			if (!empty($KSGList)) {
				// Если в полученном перечне есть терапевтические КСГ, то для каждой из них производится проверка на наличие в перечне связанной с ней хирургической КСГ.
				// Если такая хирургическая КСГ есть, то терапевтическая КСГ удаляется из перечня
				$Mes_ids = array_keys($KSGList);
				$resp_ml = $this->queryResult("
					select
						Mes_id
					from
						v_MesLink (nolock)
					where
						Mes_id IN ('".implode("','", $Mes_ids)."') and
						Mes_sid IN ('".implode("','", $Mes_ids)."') and
						MesLink_begDT <= :EvnSection_disDate and
						ISNULL(MesLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate and
						MesLinkType_id = 2
				", array(
					'EvnSection_disDate' => $data['EvnSection_disDate']
				));
				foreach($resp_ml as $one_ml) {
					unset($KSGList[$one_ml['Mes_id']]); // терапевтическая КСГ удаляется из перечня
				}

				// ищем КСГ, которая будет выдаваться по умолчанию
				$deafultKSG = null;

				// сперва среди тех, которые определены с учётом иного классификационного критерия
				foreach($KSGList as $oneKSG) {
					if (!empty($oneKSG['dopCriteriaCount']) && (empty($deafultKSG) || $deafultKSG['MesTariff_Value'] < $oneKSG['MesTariff_Value'])) {
						$deafultKSG = $oneKSG;
					}
				}

				if ( empty($deafultKSG) ) {
					// затем среди остальных
					foreach($KSGList as $oneKSG) {
						if (empty($deafultKSG) || $deafultKSG['MesTariff_Value'] < $oneKSG['MesTariff_Value']) {
							$deafultKSG = $oneKSG;
						}
					}
				}

				foreach($KSGList as $oneKSG) {
					$combovalues[] = array(
						'Mes_Code' => $oneKSG['Mes_Code'],
						'Mes_Name' => $oneKSG['Mes_Name'],
						'MesOld_Num' => $oneKSG['MesOld_Num'],
						'Mes_tid' => !empty($oneKSG['isTerr']) ? $oneKSG['Mes_id'] : null,
						'Mes_sid' => empty($oneKSG['isTerr']) ? $oneKSG['Mes_id'] : null,
						'MesTariff_Value' => $oneKSG['MesTariff_Value'],
						'MesTariff_id' => $oneKSG['MesTariff_id'],
						'MesType_id' => $oneKSG['MesType_id'],
						'Mes_id' => $oneKSG['Mes_id'],
						'MesOldUslugaComplex_id' => $oneKSG['MesOldUslugaComplex_id'],
						'Mes_IsDefault' => (!empty($deafultKSG) && $deafultKSG['Mes_id'] == $oneKSG['Mes_id']) ? 2 : 1
					);
				}
			}
		} else {
			$combovalues[] = array(
				'Mes_Code' => $KSGFromPolyTrauma['Mes_Code'],
				'Mes_Name' => $KSGFromPolyTrauma['Mes_Name'],
				'MesOld_Num' => $KSGFromPolyTrauma['MesOld_Num'],
				'Mes_tid' => $KSGFromPolyTrauma['Mes_id'],
				'Mes_sid' => null,
				'MesTariff_Value' => $KSGFromPolyTrauma['MesTariff_Value'],
				'MesTariff_id' => $KSGFromPolyTrauma['MesTariff_id'],
				'MesType_id' => $KSGFromPolyTrauma['MesType_id'],
				'Mes_id' => $KSGFromPolyTrauma['Mes_id'],
				'MesOldUslugaComplex_id' => $KSGFromPolyTrauma['MesOldUslugaComplex_id'],
				'Mes_IsDefault' => 2
			);
		}
		
		return $combovalues;
	}

	/**
	 * Получение комбо КСГ/КПГ/Коэффициента КСГ/КПГ для 2018 года
	 */
	function loadKSGKPGKOEFCombo2018($data) {
		$combovalues = array();
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
				if (substr($data['LastEvnSection_disDate'], 0, 4) >= '2019') {
					return $this->loadKSGKPGKOEFCombo2019($data);
				}
			}
		}

		// считаем длительность пребывания
		$datediff = strtotime($data['EvnSection_disDate']) - strtotime($data['EvnSection_setDate']);
		$data['Duration'] = floor($datediff/(60*60*24)) + 1; // refs #128554

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
		$MesAgeGroup11FilterVolume = "or (:Person_Age < 2 and av2.AttributeValue_ValueIdent = 11)";
		if ($data['BirthToday'] == 1) {
			// если сегодня д.р. то условия другие
			$MesAgeGroup11Filter = "or (:Person_Age <= 2 and mu.MesAgeGroup_id = 11)";
			$MesAgeGroup11FilterVolume = "or (:Person_Age <= 2 and av2.AttributeValue_ValueIdent = 11)";
		}

		$data['VolumeType_Code'] = '3';
		$addFilter = "";
		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			$data['VolumeType_Code'] = '1';
			$addFilter = "
				cross apply(
					select top 1
						av2.AttributeValue_ValueIdent
					from
						v_AttributeValue av2 (nolock)
						inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
					where
						av2.AttributeValue_rid = av.AttributeValue_id
						and a2.Attribute_TableName = 'dbo.LpuUnitType'
						and ISNULL(av2.AttributeValue_ValueIdent, :LpuUnitType_id) = :LpuUnitType_id
				) LUTFILTER
			";
		}

		// объёмы по новой таблице
		$data['VolumeType_id'] = $this->getFirstResultFromQuery("select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = :VolumeType_Code", array(
			'VolumeType_Code' => $data['VolumeType_Code']
		), true);

		$crossapplymesvol = ""; // отключил объёмы по задаче #102954
		/*
		$crossapplymesvol = "
			cross apply (
				SELECT  TOP 1
					av.AttributeValue_id
				FROM
					v_AttributeVision avis (nolock)
					inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
					inner join v_Attribute a (nolock) on a.Attribute_id = av.Attribute_id
					cross apply(
						select top 1
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2 (nolock)
							inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.Lpu'
							and ISNULL(av2.AttributeValue_ValueIdent, :Lpu_id) = :Lpu_id
					) MOFILTER
					cross apply(
						select top 1
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2 (nolock)
							inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.MesOld'
							and av2.AttributeValue_ValueIdent = mo.Mes_id -- КСГ или КПГ должен быть указан
					) KSGFILTER
					cross apply(
						select top 1
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2 (nolock)
							inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.MesAgeGroup'
							and (
								(:Person_Age >= 18 and av2.AttributeValue_ValueIdent = 1)
								or (:Person_Age < 18 and av2.AttributeValue_ValueIdent = 2)
								or (:Person_AgeDays > 28 and av2.AttributeValue_ValueIdent = 3)
								or (:Person_AgeDays <= 28 and av2.AttributeValue_ValueIdent = 4)
								or (:Person_Age < 18 and av2.AttributeValue_ValueIdent = 5)
								or (:Person_Age >= 18 and av2.AttributeValue_ValueIdent = 6)
								or (:Person_Age < 8 and av2.AttributeValue_ValueIdent = 7)
								or (:Person_Age >= 8 and av2.AttributeValue_ValueIdent = 8)
								or (:Person_AgeDays <= 90 and av2.AttributeValue_ValueIdent = 9)
								or (:Person_Age <= 1 and av2.AttributeValue_ValueIdent = 10)
								{$MesAgeGroup11FilterVolume}
								or (av2.AttributeValue_ValueIdent IS NULL)
							)
					) MESAGEFILTER
					{$addFilter}
				WHERE
					avis.AttributeVision_TableName = 'dbo.VolumeType'
					and avis.AttributeVision_TablePKey = :VolumeType_id
					and avis.AttributeVision_IsKeyValue = 2
					and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
					and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
			) MESVOL
		";
		*/

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
								2 as MesType_id,
								mo.Mes_Code as Mes_Code,
								mo.Mes_Name,
								mo.Mes_id,
								mt.MesTariff_Value as MesTariff_Value,
								mt.MesTariff_id
							from
								v_MesOld mo (nolock)
								{$crossapplymesvol}
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

		$UslugaComplexIds = array();
		if (!empty($data['EvnSection_id'])) {
			$query = "
				select distinct
					uc.UslugaComplex_id,
					uc.UslugaComplex_Code
				from
					v_EvnUsluga eu (nolock)
					inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = :PayTypeOms_id
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

		$KSGOperArray = array();
		// 1. Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
		if (!empty($UslugaComplexIds)) {
			$query = "
				select top 100
					2 as MesType_id,
					uc.UslugaComplex_id,
					mu.MesOldUslugaComplex_id,
					mo.Mes_Code as Mes_Code,
					mo.Mes_Name,
					mo.Mes_id,
					mt.MesTariff_Value as MesTariff_Value,
					mt.MesTariff_id,
					case when mu.DrugTherapyScheme_id is not null then 1 else 0 end + case when mu.RehabScale_id is not null then 1 else 0 end as dopCriteriaCount
				from v_UslugaComplex uc (nolock)
					inner join v_MesOldUslugaComplex mu (nolock) on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					{$crossapplymesvol}
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					uc.UslugaComplex_id IN ('".implode("','", $UslugaComplexIds)."')
					and (
						(mu.Diag_id IS NULL) OR
						(mu.Diag_id = :Diag_id and mu.MesOldUslugaComplex_IsDiag = 2) OR
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
					and (IsNull(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and ISNULL(mo.MesType_id, :MesType_id) = :MesType_id
					and ISNULL(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				order by
					uc.UslugaComplex_id,
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
					$KSGOper = $resp[0];
					$CurUsluga = $resp[0]['UslugaComplex_id'];
					// ищем максимальную КСГ среди разных услуг.
					foreach($KSGOperArray as $KSGOperOne) {
						if ($CurUsluga != $KSGOperOne['UslugaComplex_id']) {
							$CurUsluga = $KSGOperOne['UslugaComplex_id'];
							if ($KSGOperOne['MesTariff_Value'] > $KSGOper['MesTariff_Value']) {
								$KSGOper = $KSGOperOne;
							}
						}
					}
				}
			}
		}

		// 2. Пробуем определить КСГ по наличию диагноза, иначе
		if (!empty($data['Diag_id'])) {
			$ucfilter = "and mu.UslugaComplex_id is null";
			$ucin = array();
			if (!empty($data['EvnSection_id'])) {
				$uc_resp = $this->queryResult("select eu.UslugaComplex_id from v_EvnUsluga eu (nolock) where eu.EvnUsluga_pid = :EvnSection_id and eu.PayType_id = :PayTypeOms_id", array(
					'EvnSection_id' => $data['EvnSection_id'],
					'PayTypeOms_id' => $data['PayTypeOms_id']
				));
				foreach ($uc_resp as $uc) {
					$ucin[] = $uc['UslugaComplex_id'];
				}

				if (count($ucin) > 0) {
					$ucfilter = "and (mu.UslugaComplex_id is null OR mu.UslugaComplex_id IN ('".implode("','", $ucin)."'))";
				}
			}

			$query = "
				select top 100
					3 as MesType_id,
					d.Diag_Code,
					mu.MesOldUslugaComplex_id,
					mo.Mes_Code as Mes_Code,
					mo.Mes_Name,
					mo.Mes_id,
					mt.MesTariff_Value as MesTariff_Value,
					mt.MesTariff_id,
					case when mu.DrugTherapyScheme_id is not null then 1 else 0 end + case when mu.RehabScale_id is not null then 1 else 0 end as dopCriteriaCount
				from v_MesOldUslugaComplex mu (nolock)
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					{$crossapplymesvol}
					left join v_Diag d (nolock) on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					mu.Diag_id = :Diag_id
					{$ucfilter}
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
					$KSGTerr = $resp[0];
					foreach($resp as $respone) {
						if ($respone['Mes_Code'] != $KSGTerr['Mes_Code'] && preg_replace('/\..*/','',$respone['Mes_Code']) == $KSGTerr['Mes_Code']) {
							$combovalues[] = $respone;
						}
					}
				}
			}
		}

		// 3.2	Пробуем определить КПГ по профилю отделения
		if (!empty($data['LpuSectionProfile_id'])) {
			$query = "
				select top 1
					4 as MesType_id,
					mo.Mes_Code as Mes_Code,
					mo.Mes_Name,
					mo.Mes_id,
					mtkpg.MesTariff_Value as MesTariff_Value,
					mtkpg.MesTariff_id
				from MesOld mo (nolock)
					inner join v_MesTariff mtkpg (nolock) on mtkpg.Mes_id = mo.Mes_id -- Коэффициент КПГ
					{$crossapplymesvol}
				where
					mo.LpuSectionProfile_id = :LpuSectionProfile_id and mo.MesType_id = 4 -- КПГ
					and mo.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mo.Mes_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mtkpg.MesTariff_begDT <= :EvnSection_disDate
					and (IsNull(mtkpg.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
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

		if ($KSGOper['Mes_id'] == $KSGTerr['Mes_id']) {
			$KSGTerr = false;
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

			if (!empty($data['MesLink_id'])) {
				$KSGTerr = false; // Если определились КСГ по диагнозу и услуге, у которых есть связка в MesLink, то КСГ по диагнозу выводится не должна.
			}
		}

		if (isset($KSGOper['dopCriteriaCount']) && isset($KSGTerr['dopCriteriaCount'])) {
			if ($KSGOper['dopCriteriaCount'] > $KSGTerr['dopCriteriaCount']) {
				$KSGOper['Mes_IsDefault'] = 2;
			}
		}

		if ($KSGOper) {
			unset($KSGOper['dopCriteriaCount']);
			$combovalues[] = $KSGOper;
		}

		if ($KSGTerr) {
			unset($KSGTerr['dopCriteriaCount']);
			$combovalues[] = $KSGTerr;
		}

		if ($KSGFromPolyTrauma) {
			$combovalues[] = $KSGFromPolyTrauma;
		}

		if ($KPGFromLpuSectionProfile) {
			$combovalues[] = $KPGFromLpuSectionProfile;
		}

		return $combovalues;
	}

	/**
	 * Получение комбо КСГ/КПГ/Коэффициента КСГ/КПГ для 2017 года
	 */
	function loadKSGKPGKOEFCombo2017($data) {
		$combovalues = array();
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
		$MesAgeGroup11FilterVolume = "or (:Person_Age < 2 and av2.AttributeValue_ValueIdent = 11)";
		if ($data['BirthToday'] == 1) {
			// если сегодня д.р. то условия другие
			$MesAgeGroup11Filter = "or (:Person_Age <= 2 and mu.MesAgeGroup_id = 11)";
			$MesAgeGroup11FilterVolume = "or (:Person_Age <= 2 and av2.AttributeValue_ValueIdent = 11)";
		}

		$data['VolumeType_Code'] = '3';
		$addFilter = "";
		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			$data['VolumeType_Code'] = '1';
			$addFilter = "
				cross apply(
					select top 1
						av2.AttributeValue_ValueIdent
					from
						v_AttributeValue av2 (nolock)
						inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
					where
						av2.AttributeValue_rid = av.AttributeValue_id
						and a2.Attribute_TableName = 'dbo.LpuUnitType'
						and ISNULL(av2.AttributeValue_ValueIdent, :LpuUnitType_id) = :LpuUnitType_id
				) LUTFILTER
			";
		}

		// объёмы по новой таблице
		$data['VolumeType_id'] = $this->getFirstResultFromQuery("select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = :VolumeType_Code", array(
			'VolumeType_Code' => $data['VolumeType_Code']
		), true);

		$crossapplymesvol = ""; // отключил объёмы по задаче #102954
		/*
		$crossapplymesvol = "
			cross apply (
				SELECT  TOP 1
					av.AttributeValue_id
				FROM
					v_AttributeVision avis (nolock)
					inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
					inner join v_Attribute a (nolock) on a.Attribute_id = av.Attribute_id
					cross apply(
						select top 1
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2 (nolock)
							inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.Lpu'
							and ISNULL(av2.AttributeValue_ValueIdent, :Lpu_id) = :Lpu_id
					) MOFILTER
					cross apply(
						select top 1
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2 (nolock)
							inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.MesOld'
							and av2.AttributeValue_ValueIdent = mo.Mes_id -- КСГ или КПГ должен быть указан
					) KSGFILTER
					cross apply(
						select top 1
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2 (nolock)
							inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.MesAgeGroup'
							and (
								(:Person_Age >= 18 and av2.AttributeValue_ValueIdent = 1)
								or (:Person_Age < 18 and av2.AttributeValue_ValueIdent = 2)
								or (:Person_AgeDays > 28 and av2.AttributeValue_ValueIdent = 3)
								or (:Person_AgeDays <= 28 and av2.AttributeValue_ValueIdent = 4)
								or (:Person_Age < 18 and av2.AttributeValue_ValueIdent = 5)
								or (:Person_Age >= 18 and av2.AttributeValue_ValueIdent = 6)
								or (:Person_Age < 8 and av2.AttributeValue_ValueIdent = 7)
								or (:Person_Age >= 8 and av2.AttributeValue_ValueIdent = 8)
								or (:Person_AgeDays <= 90 and av2.AttributeValue_ValueIdent = 9)
								or (:Person_Age <= 1 and av2.AttributeValue_ValueIdent = 10)
								{$MesAgeGroup11FilterVolume}
								or (av2.AttributeValue_ValueIdent IS NULL)
							)
					) MESAGEFILTER
					{$addFilter}
				WHERE
					avis.AttributeVision_TableName = 'dbo.VolumeType'
					and avis.AttributeVision_TablePKey = :VolumeType_id
					and avis.AttributeVision_IsKeyValue = 2
					and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
					and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
			) MESVOL
		";
		*/

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
								2 as MesType_id,
								mo.Mes_Code as Mes_Code,
								mo.Mes_Name,
								mo.Mes_id,
								mt.MesTariff_Value as MesTariff_Value,
								mt.MesTariff_id
							from
								v_MesOld mo (nolock)
								{$crossapplymesvol}
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
					2 as MesType_id,
					eu.EvnUsluga_id,
					mo.Mes_Code as Mes_Code,
					mo.Mes_Name,
					mo.Mes_id,
					mt.MesTariff_Value as MesTariff_Value,
					mt.MesTariff_id
				from v_EvnUsluga eu (nolock)
					inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu (nolock) on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					{$crossapplymesvol}
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = :PayTypeOms_id
					and (
						(mu.Diag_id IS NULL) OR
						(mu.Diag_id = :Diag_id and mu.MesOldUslugaComplex_IsDiag = 2) OR
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
						or (:Person_AgeDays > 90 and :Person_Age < 1 and mu.MesAgeGroup_id = 10)
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
					$KSGOper = $resp[0];
					$CurUsluga = $resp[0]['EvnUsluga_id'];
					// ищем максимальную КСГ среди разных услуг.
					foreach($KSGOperArray as $KSGOperOne) {
						if ($CurUsluga != $KSGOperOne['EvnUsluga_id']) {
							$CurUsluga = $KSGOperOne['EvnUsluga_id'];
							if ($KSGOperOne['MesTariff_Value'] > $KSGOper['MesTariff_Value']) {
								$KSGOper = $KSGOperOne;
							}
						}
					}
				}
			}
		}

		// 2. Пробуем определить КСГ по наличию диагноза, иначе
		if (!empty($data['Diag_id'])) {
			$ucfilter = "and mu.UslugaComplex_id is null";
			$ucin = array();
			if (!empty($data['EvnSection_id'])) {
				$uc_resp = $this->queryResult("select eu.UslugaComplex_id from v_EvnUsluga eu (nolock) where eu.EvnUsluga_pid = :EvnSection_id and eu.PayType_id = :PayTypeOms_id", array(
					'EvnSection_id' => $data['EvnSection_id'],
					'PayTypeOms_id' => $data['PayTypeOms_id']
				));
				foreach ($uc_resp as $uc) {
					$ucin[] = $uc['UslugaComplex_id'];
				}

				if (count($ucin) > 0) {
					$ucfilter = "and (mu.UslugaComplex_id is null OR mu.UslugaComplex_id IN ('".implode("','", $ucin)."'))";
				}
			}

			$query = "
				select top 100
					3 as MesType_id,
					d.Diag_Code,
					mo.Mes_Code as Mes_Code,
					mo.Mes_Name,
					mo.Mes_id,
					mt.MesTariff_Value as MesTariff_Value,
					mt.MesTariff_id
				from v_MesOldUslugaComplex mu (nolock)
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					{$crossapplymesvol}
					left join v_Diag d (nolock) on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					mu.Diag_id = :Diag_id
					{$ucfilter}
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
						or (:Person_AgeDays > 90 and :Person_Age < 1 and mu.MesAgeGroup_id = 10)
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
					$KSGTerr = $resp[0];
					foreach($resp as $respone) {
						if ($respone['Mes_Code'] != $KSGTerr['Mes_Code'] && preg_replace('/\..*/','',$respone['Mes_Code']) == $KSGTerr['Mes_Code']) {
							$combovalues[] = $respone;
						}
					}
				}
			}
		}

		// 3.2	Пробуем определить КПГ по профилю отделения
		if (!empty($data['LpuSectionProfile_id'])) {
			$query = "
				select top 1
					4 as MesType_id,
					mo.Mes_Code as Mes_Code,
					mo.Mes_Name,
					mo.Mes_id,
					mtkpg.MesTariff_Value as MesTariff_Value,
					mtkpg.MesTariff_id
				from MesOld mo (nolock)
					inner join v_MesTariff mtkpg (nolock) on mtkpg.Mes_id = mo.Mes_id -- Коэффициент КПГ
					{$crossapplymesvol}
				where
					mo.LpuSectionProfile_id = :LpuSectionProfile_id and mo.MesType_id = 4 -- КПГ
					and mo.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mo.Mes_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mtkpg.MesTariff_begDT <= :EvnSection_disDate
					and (IsNull(mtkpg.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
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

		if ($KSGOper['Mes_id'] == $KSGTerr['Mes_id']) {
			$KSGTerr = false;
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

			if (!empty($data['MesLink_id'])) {
				$KSGTerr = false; // Если определились КСГ по диагнозу и услуге, у которых есть связка в MesLink, то КСГ по диагнозу выводится не должна.
			}
		}

		if ($KSGOper) {
			$combovalues[] = $KSGOper;
		}

		if ($KSGTerr) {
			$combovalues[] = $KSGTerr;
		}

		if ($KSGFromPolyTrauma) {
			$combovalues[] = $KSGFromPolyTrauma;
		}

		if ($KPGFromLpuSectionProfile) {
			$combovalues[] = $KPGFromLpuSectionProfile;
		}

		return $combovalues;
	}

	/**
	 * Получение комбо КСГ/КПГ/Коэффициента КСГ/КПГ для 2016 года
	 */
	function loadKSGKPGKOEFCombo2016($data) {
		$combovalues = array();
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

		$data['VolumeType_Code'] = '3';
		$addFilter = "";
		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			$data['VolumeType_Code'] = '1';
			$addFilter = "
				cross apply(
					select top 1
						av2.AttributeValue_ValueIdent
					from
						v_AttributeValue av2 (nolock)
						inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
					where
						av2.AttributeValue_rid = av.AttributeValue_id
						and a2.Attribute_TableName = 'dbo.LpuUnitType'
						and ISNULL(av2.AttributeValue_ValueIdent, :LpuUnitType_id) = :LpuUnitType_id
				) LUTFILTER
			";
		}

		// объёмы по новой таблице в 2016 году
		$data['VolumeType_id'] = $this->getFirstResultFromQuery("select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = :VolumeType_Code", array(
			'VolumeType_Code' => $data['VolumeType_Code']
		), true);

		$crossapplymesvol = "
			cross apply (
				SELECT  TOP 1
					av.AttributeValue_id
				FROM
					v_AttributeVision avis (nolock)
					inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
					inner join v_Attribute a (nolock) on a.Attribute_id = av.Attribute_id
					cross apply(
						select top 1
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2 (nolock)
							inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.Lpu'
							and ISNULL(av2.AttributeValue_ValueIdent, :Lpu_id) = :Lpu_id
					) MOFILTER
					cross apply(
						select top 1
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2 (nolock)
							inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.MesOld'
							and av2.AttributeValue_ValueIdent = mo.Mes_id -- КСГ или КПГ должен быть указан
					) KSGFILTER
					cross apply(
						select top 1
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2 (nolock)
							inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.MesAgeGroup'
							and (
								(:Person_Age >= 18 and av2.AttributeValue_ValueIdent = 1)
								or (:Person_Age < 18 and av2.AttributeValue_ValueIdent = 2)
								or (:Person_AgeDays > 28 and av2.AttributeValue_ValueIdent = 3)
								or (:Person_AgeDays <= 28 and av2.AttributeValue_ValueIdent = 4)
								or (:Person_Age < 18 and av2.AttributeValue_ValueIdent = 5)
								or (:Person_Age >= 18 and av2.AttributeValue_ValueIdent = 6)
								or (:Person_Age < 8 and av2.AttributeValue_ValueIdent = 7)
								or (:Person_Age >= 8 and av2.AttributeValue_ValueIdent = 8)
								or (:Person_AgeDays <= 90 and av2.AttributeValue_ValueIdent = 9)
								or (:Person_Age <= 1 and av2.AttributeValue_ValueIdent = 10)
								or (av2.AttributeValue_ValueIdent IS NULL)
							)
					) MESAGEFILTER
					{$addFilter}
				WHERE
					avis.AttributeVision_TableName = 'dbo.VolumeType'
					and avis.AttributeVision_TablePKey = :VolumeType_id
					and avis.AttributeVision_IsKeyValue = 2
					and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
					and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
			) MESVOL
		";

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
								2 as MesType_id,
								mo.Mes_Code as Mes_Code,
								mo.Mes_Name,
								mo.Mes_id,
								mt.MesTariff_Value as MesTariff_Value,
								mt.MesTariff_id
							from
								v_MesOld mo (nolock)
								{$crossapplymesvol}
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
					2 as MesType_id,
					eu.EvnUsluga_id,
					mo.Mes_Code as Mes_Code,
					mo.Mes_Name,
					mo.Mes_id,
					mt.MesTariff_Value as MesTariff_Value,
					mt.MesTariff_id
				from v_EvnUsluga eu (nolock)
					inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu (nolock) on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					{$crossapplymesvol}
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = :PayTypeOms_id
					and (
						(mu.Diag_id IS NULL) OR
						(mu.Diag_id = :Diag_id and mu.MesOldUslugaComplex_IsDiag = 2) OR
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
						or (:Person_AgeDays > 90 and :Person_Age < 1 and mu.MesAgeGroup_id = 10)
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
					$KSGOper = $resp[0];
					$CurUsluga = $resp[0]['EvnUsluga_id'];
					// ищем максимальную КСГ среди разных услуг.
					foreach($KSGOperArray as $KSGOperOne) {
						if ($CurUsluga != $KSGOperOne['EvnUsluga_id']) {
							$CurUsluga = $KSGOperOne['EvnUsluga_id'];
							if ($KSGOperOne['MesTariff_Value'] > $KSGOper['MesTariff_Value']) {
								$KSGOper = $KSGOperOne;
							}
						}
					}
				}
			}
		}

		// 2. Пробуем определить КСГ по наличию диагноза, иначе
		if (!empty($data['Diag_id'])) {
			$ucfilter = "and mu.UslugaComplex_id is null";
			$ucin = array();
			if (!empty($data['EvnSection_id'])) {
				$uc_resp = $this->queryResult("select eu.UslugaComplex_id from v_EvnUsluga eu (nolock) where eu.EvnUsluga_pid = :EvnSection_id and eu.PayType_id = :PayTypeOms_id", array(
					'EvnSection_id' => $data['EvnSection_id'],
					'PayTypeOms_id' => $data['PayTypeOms_id']
				));
				foreach ($uc_resp as $uc) {
					$ucin[] = $uc['UslugaComplex_id'];
				}

				if (count($ucin) > 0) {
					$ucfilter = "and (mu.UslugaComplex_id is null OR mu.UslugaComplex_id IN ('".implode("','", $ucin)."'))";
				}
			}

			$query = "
				select top 100
					3 as MesType_id,
					d.Diag_Code,
					mo.Mes_Code as Mes_Code,
					mo.Mes_Name,
					mo.Mes_id,
					mt.MesTariff_Value as MesTariff_Value,
					mt.MesTariff_id
				from v_MesOldUslugaComplex mu (nolock)
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					{$crossapplymesvol}
					left join v_Diag d (nolock) on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					mu.Diag_id = :Diag_id
					{$ucfilter}
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
						or (:Person_AgeDays > 90 and :Person_Age < 1 and mu.MesAgeGroup_id = 10)
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
					$KSGTerr = $resp[0];
					foreach($resp as $respone) {
						if ($respone['Mes_Code'] != $KSGTerr['Mes_Code'] && preg_replace('/\..*/','',$respone['Mes_Code']) == $KSGTerr['Mes_Code']) {
							$combovalues[] = $respone;
						}
					}
				}
			}
		}

		// 3.2	Пробуем определить КПГ по профилю отделения
		if (!empty($data['LpuSectionProfile_id'])) {
			$query = "
				select top 1
					4 as MesType_id,
					mo.Mes_Code as Mes_Code,
					mo.Mes_Name,
					mo.Mes_id,
					mtkpg.MesTariff_Value as MesTariff_Value,
					mtkpg.MesTariff_id
				from MesOld mo (nolock)
					inner join v_MesTariff mtkpg (nolock) on mtkpg.Mes_id = mo.Mes_id -- Коэффициент КПГ
					{$crossapplymesvol}
				where
					mo.LpuSectionProfile_id = :LpuSectionProfile_id and mo.MesType_id = 4 -- КПГ
					and mo.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mo.Mes_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mtkpg.MesTariff_begDT <= :EvnSection_disDate
					and (IsNull(mtkpg.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
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

		if ($KSGOper['Mes_id'] == $KSGTerr['Mes_id']) {
			$KSGTerr = false;
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

			if (!empty($data['MesLink_id'])) {
				$KSGTerr = false; // Если определились КСГ по диагнозу и услуге, у которых есть связка в MesLink, то КСГ по диагнозу выводится не должна.
			}
		}

		if ($KSGOper) {
			$combovalues[] = $KSGOper;
		}

		if ($KSGTerr) {
			$combovalues[] = $KSGTerr;
		}

		if ($KSGFromPolyTrauma) {
			$combovalues[] = $KSGFromPolyTrauma;
		}

		if ($KPGFromLpuSectionProfile) {
			$combovalues[] = $KPGFromLpuSectionProfile;
		}

		return $combovalues;
	}


	/**
	 * Получение комбо КСГ/КПГ/Коэффициента КСГ/КПГ для 2015 года
	 */
	function loadKSGKPGKOEFCombo2015($data) {
		$combovalues = array();
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

		if (substr($data['EvnSection_disDate'], 0, 4) >= '2016') {
			// алгоритм с 2015 года
			return $this->loadKSGKPGKOEFCombo2016($data);
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

		$crossapplymesvol = $this->getCrossApplyMesVol($data);

		// если задан профиль, то для КСГ должна быть связь с КПГ с данным профилем
		$crossapply = "";
		// убрал по задаче #56448.
		/*
		if (!empty($data['LpuSectionProfile_id'])) {
			$crossapply = "
				cross apply(
					select top 1
						mo2.Mes_id as Mes_kid,
						mo2.Mes_Code as KPG
					from
						v_MesOld mo2 (nolock)
						inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
					where
						mo2.LpuSectionProfile_id = :LpuSectionProfile_id and mo2.MesType_id = 4
						and ml.Mes_id = mo.Mes_id
						and mo2.Mes_begDT <= :EvnSection_disDate
						and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
				) mokpg
			";
		}*/

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
							and d.Diag_Code IN ('J95.1', 'J95.2', 'J96.0', 'N17.0', 'N17.1', 'N17.2', 'N17.8', 'N17.9', 'T79.4', 'R57.1', 'R57.8')
					)
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					if($resp[0]['PolyTrauma_Code'] == 7 || !empty($resp[0]['EvnDiagPS_id'])) {
						$query = "
							select top 1
								2 as MesType_id,
								mo.Mes_Code as Mes_Code,
								mo.Mes_Name,
								mo.Mes_id,
								mt.MesTariff_Value as MesTariff_Value,
								mt.MesTariff_id,
								MESV.MesAgeGroup_id
							from
								v_MesOld mo (nolock)
								{$crossapply}
								{$crossapplymesvol}
								left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
							where
								mo.Mes_Code = '192'
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
		if (!empty($data['EvnSection_id'])) {
			$query = "
				select top 100
					2 as MesType_id,
					eu.EvnUsluga_id,
					mo.Mes_Code as Mes_Code,
					mo.Mes_Name,
					mo.Mes_id,
					mt.MesTariff_Value as MesTariff_Value,
					mt.MesTariff_id,
					MESV.MesAgeGroup_id
				from v_EvnUsluga eu (nolock)
					inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu (nolock) on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					{$crossapply}
					{$crossapplymesvol}
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = :PayTypeOms_id
					and (
						(mu.Diag_id IS NULL) OR
						(mu.Diag_id = :Diag_id and mu.MesOldUslugaComplex_IsDiag = 2) OR
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
						or (:Person_AgeDays > 90 and :Person_Age < 1 and mu.MesAgeGroup_id = 10)
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
				order by
					eu.EvnUsluga_id,
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
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
					$KSGOper = $resp[0];
					$CurUsluga = $resp[0]['EvnUsluga_id'];
					// ищем максимальную КСГ среди разных услуг.
					foreach($KSGOperArray as $KSGOperOne) {
						if ($CurUsluga != $KSGOperOne['EvnUsluga_id']) {
							$CurUsluga = $KSGOperOne['EvnUsluga_id'];
							if ($KSGOperOne['MesTariff_Value'] > $KSGOper['MesTariff_Value']) {
								$KSGOper = $KSGOperOne;
							}
						}
					}
				}
			}
		}

		// 2. Пробуем определить КСГ по наличию диагноза, иначе
		if (!empty($data['Diag_id'])) {
			$diagfilter = "mu.Diag_id = :Diag_id";
			$diagin = array();
			if (!empty($data['EvnSection_id'])) {
				$diag_resp = $this->queryResult("select Diag_id from v_EvnDiagPS (nolock) where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id", array(
					'EvnSection_id' => $data['EvnSection_id']
				));
				foreach ($diag_resp as $diag) {
					$diagin[] = $diag['Diag_id'];
				}

				if (count($diagin) > 0) {
					$diagfilter = "(mu.Diag_id = :Diag_id OR (mo.Mes_Code in ('44','92') and mu.Diag_id IN ('".implode("','", $diagin)."')))";
				}
			}

			$ucfilter = "and mu.UslugaComplex_id is null";
			$ucin = array();
			if (!empty($data['EvnSection_id'])) {
				$uc_resp = $this->queryResult("select eu.UslugaComplex_id from v_EvnUsluga eu (nolock) where eu.EvnUsluga_pid = :EvnSection_id and eu.PayType_id = :PayTypeOms_id", array(
					'EvnSection_id' => $data['EvnSection_id'],
					'PayTypeOms_id' => $data['PayTypeOms_id']
				));
				foreach ($uc_resp as $uc) {
					$ucin[] = $uc['UslugaComplex_id'];
				}

				if (count($ucin) > 0) {
					$ucfilter = "and (mu.UslugaComplex_id is null OR mu.UslugaComplex_id IN ('".implode("','", $ucin)."'))";
				}
			}

			$query = "
				select top 100
					3 as MesType_id,
					d.Diag_Code,
					mo.Mes_Code as Mes_Code,
					mo.Mes_Name,
					mo.Mes_id,
					mt.MesTariff_Value as MesTariff_Value,
					mt.MesTariff_id,
					MESV.MesAgeGroup_id
				from v_MesOldUslugaComplex mu (nolock)
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					{$crossapply}
					{$crossapplymesvol}
					left join v_Diag d (nolock) on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					{$diagfilter}
					{$ucfilter}
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
				order by
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
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
					$KSGTerr = $resp[0];
					foreach($resp as $respone) {
						if ($respone['Mes_Code'] != $KSGTerr['Mes_Code'] && preg_replace('/\..*/','',$respone['Mes_Code']) == $KSGTerr['Mes_Code']) {
							$combovalues[] = $respone;
						}
					}
				}
			}
		}

		// 3.2	Пробуем определить КПГ по профилю отделения
		if (!empty($data['LpuSectionProfile_id'])) {
			$query = "
				select top 1
					4 as MesType_id,
					mokpg.Mes_Code as Mes_Code,
					mokpg.Mes_Code + ' ' + mokpg.Mes_Name as Mes_Name,
					mokpg.Mes_id,
					mtkpg.MesTariff_Value as MesTariff_Value,
					mtkpg.MesTariff_id,
					mokpg.MesAgeGroup_id
				from MesOld mokpg (nolock)
					inner join v_MesTariff mtkpg (nolock) on mtkpg.Mes_id = mokpg.Mes_id -- Коэффициент КПГ
				where
					mokpg.LpuSectionProfile_id = :LpuSectionProfile_id and mokpg.MesType_id = 4 -- КПГ
					and mokpg.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mokpg.Mes_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mtkpg.MesTariff_begDT <= :EvnSection_disDate
					and (IsNull(mtkpg.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
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

		// Если при определении КСГ для ребенка мы смотрели объемы для взрослых и у КПГ проставлена детская возрастная группа, то по умолчанию подставляем в поле КПГ.
		$isKPGDefault = true;
		if ($data['Person_Age'] >= 18) {
			$isKPGDefault = false;
		}

		if ($KSGOper) {
			$combovalues[] = $KSGOper;

			if ($KSGOper['MesAgeGroup_id'] != 1) {
				$isKPGDefault = false;
			}
		}

		if ($KSGTerr) {
			$combovalues[] = $KSGTerr;

			if ($KSGTerr['MesAgeGroup_id'] != 1) {
				$isKPGDefault = false;
			}
		}

		if ($KSGFromPolyTrauma) {
			$combovalues[] = $KSGFromPolyTrauma;

			if ($KSGFromPolyTrauma['MesAgeGroup_id'] != 1) {
				$isKPGDefault = false;
			}
		}

		if ($KPGFromLpuSectionProfile) {
			if ($isKPGDefault && $KPGFromLpuSectionProfile['MesAgeGroup_id'] == 2) {
				$KPGFromLpuSectionProfile['Mes_IsDefault'] = 2;
			}

			$combovalues[] = $KPGFromLpuSectionProfile;
		}

		// для каждого КСГ/КПГ смотрим есть ли объёмы по реабилитации
		foreach($combovalues as &$onevalue) {
			$onevalue['Mes_IsRehab'] = 1;

			// если КСГ то по связанному КПГ
			$from = "
				v_MesOld mo (nolock)
			";
			$filter = "mo.Mes_id = :Mes_id";

			if ($onevalue['MesType_id'] != 4) {
				$from = "
					v_MesLink ml (nolock)
					inner join v_MesOld mo (nolock) on mo.Mes_id = ml.Mes_sid
				";
				$filter = "
					ml.Mes_id = :Mes_id
					and ml.MesLinkType_id = 1
					and mo.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
				";
			}
			$query = "
				select top 1
					MesVol_id
				from
					{$from}
					inner join r30.v_MesVol mv (nolock) on mv.Mes_id = mo.Mes_id
				where
					{$filter}
					and mv.Lpu_id = :Lpu_id
					and (
						(:Person_Age >= 18 and mv.MesAgeGroup_id = 1)
						or (:Person_Age < 18 and mv.MesAgeGroup_id = 2)
						or (:Person_AgeDays > 28 and mv.MesAgeGroup_id = 3)
						or (:Person_AgeDays <= 28 and mv.MesAgeGroup_id = 4)
						or (:Person_Age < 18 and mv.MesAgeGroup_id = 5)
						or (:Person_Age >= 18 and mv.MesAgeGroup_id = 6)
						or (:Person_Age < 8 and mv.MesAgeGroup_id = 7)
						or (:Person_Age >= 8 and mv.MesAgeGroup_id = 8)
						or (:Person_AgeDays <= 90 and mv.MesAgeGroup_id = 9)
						or (mv.MesAgeGroup_id IS NULL)
					)
					and LpuUnitType_id = :LpuUnitType_id
					and MesVol_begDate <= :EvnSection_disDate
					and (MesVol_endDate >= :EvnSection_disDate or MesVol_endDate IS NULL)
					and MesVol_IsRehab = 2
			";
			$result = $this->db->query($query, array(
				'Mes_id' => $onevalue['Mes_id'],
				'Person_Age' => $data['Person_Age'],
				'Person_AgeDays' => $data['Person_AgeDays'],
				'Lpu_id' => $data['Lpu_id'],
				'LpuUnitType_id' => $data['LpuUnitType_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$onevalue['Mes_IsRehab'] = 2;
				}
			}
		}

		return $combovalues;
	}

	/**
	 * Получение комбо КСГ/КПГ/Коэффициента КСГ/КПГ
	 */
	function loadKSGKPGKOEFCombo($data) {
		$combovalues = array();
		$KSGFromPolyTrauma = false;
		$KSGFromUsluga = false;
		$KSGFromDiag = false;
		$KPGFromLpuSectionProfile = false;

		$data['PayTypeOms_id'] = $this->getFirstResultFromQuery("select top 1 PayType_id from v_PayType pt with (nolock) where pt.PayType_SysNick = 'oms'");
		if (empty($data['PayTypeOms_id'])) {
			return array('Error_Msg' => 'Ошибка получения идентификатора вида оплаты ОМС');
		}

		if (!empty($data['LpuSection_id'])) {
			$query = "
				select
					lu.LpuUnitType_id
				from
					v_LpuSection ls (nolock)
					left join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
				where
					ls.LpuSection_id = :LpuSection_id
			";

			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$data['LpuUnitType_id'] = $resp[0]['LpuUnitType_id'];
				}
			}
		}

		if (empty($data['LpuUnitType_id'])) {
			$data['LpuUnitType_id'] = null;
		}

		if (empty($data['EvnSection_setDate'])) {
			$data['EvnSection_setDate'] = date('Y-m-d');
		}

		if (empty($data['EvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['EvnSection_setDate'];
		}

		if (substr($data['EvnSection_disDate'], 0, 4) >= '2020') {
			// алгоритм с 2020 года
			return $this->loadKSGKPGKOEFCombo2020($data);
		} else if (substr($data['EvnSection_disDate'], 0, 4) >= '2019') {
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
		} else if (substr($data['EvnSection_disDate'], 0, 4) >= '2015') {
			// алгоритм с 2015 года
			return $this->loadKSGKPGKOEFCombo2015($data);
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
							and d.Diag_Code IN ('J95.1', 'J95.2', 'J96.0', 'N17.0', 'N17.1', 'N17.2', 'N17.8', 'N17.9', 'T79.4')
					)
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					if($resp[0]['PolyTrauma_Code'] == 7 || !empty($resp[0]['EvnDiagPS_id'])) {
						$query = "
							select top 1
								2 as MesType_id,
								mo.Mes_Code as Mes_Code,
								mo.Mes_Name,
								mo.Mes_id,
								mt.MesTariff_Value as MesTariff_Value,
								mt.MesTariff_id
							from
								v_MesOld mo (nolock)
								cross apply(
									select top 1
										MesVol_id
									from
										r30.v_MesVol mv (nolock)
									where
										mv.Mes_id = mo.Mes_id
										and mv.Lpu_id = :Lpu_id
										and (
											(:Person_Age >= 18 and mv.MesAgeGroup_id = 1)
											or (:Person_Age < 18 and mv.MesAgeGroup_id = 2)
											or (:Person_AgeDays > 28 and mv.MesAgeGroup_id = 3)
											or (:Person_AgeDays <= 28 and mv.MesAgeGroup_id = 4)
											or (:Person_Age < 18 and mv.MesAgeGroup_id = 5)
											or (:Person_Age >= 18 and mv.MesAgeGroup_id = 6)
											or (:Person_Age < 8 and mv.MesAgeGroup_id = 7)
											or (:Person_Age >= 8 and mv.MesAgeGroup_id = 8)
											or (:Person_AgeDays <= 90 and mv.MesAgeGroup_id = 9)
											or (mv.MesAgeGroup_id IS NULL)
										)
										and LpuUnitType_id = :LpuUnitType_id
										and MesVol_begDate <= :EvnSection_disDate
										and (MesVol_endDate >= :EvnSection_disDate or MesVol_endDate IS NULL)
								) MESV
								left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id and mt.MesPayType_id = 5 -- Коэффициент КСГ
							where
								mo.Mes_Code = '127' and mo.MesType_id IN (2,3,5) -- КСГ терапевтическая
								and mo.Mes_begDT <= :EvnSection_disDate
								and (IsNull(mo.Mes_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
								and mt.MesTariff_begDT <= :EvnSection_disDate
								and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						";

						$result = $this->db->query($query, $data);
						if (is_object($result)) {
							$resp = $result->result('array');
							if (count($resp) > 0) {
								$combovalues[] = $resp[0];
							}
						}
					}
				}
			}
		}

		// 1.	Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
		if (!empty($data['EvnSection_id'])) {
			$query = "
				select top 1
					2 as MesType_id,
					mo.Mes_Code as Mes_Code,
					mo.Mes_Name,
					mo.Mes_id,
					mt.MesTariff_Value as MesTariff_Value,
					mt.MesTariff_id
				from v_EvnUsluga eu (nolock)
					inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu (nolock) on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id and mo.MesType_id IN (2,3,5) -- КСГ терапевтическая
					cross apply(
						select top 1
							MesVol_id
						from
							r30.v_MesVol mv (nolock)
						where
							mv.Mes_id = mo.Mes_id
							and mv.Lpu_id = :Lpu_id
							and (
								(:Person_Age >= 18 and mv.MesAgeGroup_id = 1)
								or (:Person_Age < 18 and mv.MesAgeGroup_id = 2)
								or (:Person_AgeDays > 28 and mv.MesAgeGroup_id = 3)
								or (:Person_AgeDays <= 28 and mv.MesAgeGroup_id = 4)
								or (:Person_Age < 18 and mv.MesAgeGroup_id = 5)
								or (:Person_Age >= 18 and mv.MesAgeGroup_id = 6)
								or (:Person_Age < 8 and mv.MesAgeGroup_id = 7)
								or (:Person_Age >= 8 and mv.MesAgeGroup_id = 8)
								or (:Person_AgeDays <= 90 and mv.MesAgeGroup_id = 9)
								or (mv.MesAgeGroup_id IS NULL)
							)
							and LpuUnitType_id = :LpuUnitType_id
							and MesVol_begDate <= :EvnSection_disDate
							and (IsNull(MesVol_endDate, :EvnSection_disDate) >= :EvnSection_disDate)
					) MESV
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id and mt.MesPayType_id = 5 -- Коэффициент КСГ
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = :PayTypeOms_id
					and (
						(mu.Diag_id IS NULL) OR
						(mu.Diag_id = :Diag_id and mu.MesOldUslugaComplex_IsDiag = 2) OR
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
						or (:Person_AgeDays > 90 and :Person_Age < 1 and mu.MesAgeGroup_id = 10)
						or (mu.MesAgeGroup_id IS NULL)
					)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (IsNull(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
				order by
					mt.MesTariff_Value desc
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$combovalues[] = $resp[0];
				}
			}
		}

		// 2.	Пробуем определить КСГ по наличию диагноза, иначе
		if (!empty($data['Diag_id'])) {
			$query = "
				select top 1
					3 as MesType_id,
					d.Diag_Code,
					mo.Mes_Code as Mes_Code,
					mo.Mes_Name,
					mo.Mes_id,
					mt.MesTariff_Value as MesTariff_Value,
					mt.MesTariff_id
				from v_MesOldDiag mod (nolock)
					inner join v_MesOld mo (nolock) on mo.Mes_id = mod.Mes_id and mo.MesType_id IN (2,3,5) -- КСГ терапевтическая
					cross apply(
						select top 1
							MesVol_id
						from
							r30.v_MesVol mv (nolock)
						where
							mv.Mes_id = mo.Mes_id
							and mv.Lpu_id = :Lpu_id
							and (
								(:Person_Age >= 18 and mv.MesAgeGroup_id = 1)
								or (:Person_Age < 18 and mv.MesAgeGroup_id = 2)
								or (:Person_AgeDays > 28 and mv.MesAgeGroup_id = 3)
								or (:Person_AgeDays <= 28 and mv.MesAgeGroup_id = 4)
								or (:Person_Age < 18 and mv.MesAgeGroup_id = 5)
								or (:Person_Age >= 18 and mv.MesAgeGroup_id = 6)
								or (:Person_Age < 8 and mv.MesAgeGroup_id = 7)
								or (:Person_Age >= 8 and mv.MesAgeGroup_id = 8)
								or (:Person_AgeDays <= 90 and mv.MesAgeGroup_id = 9)
								or (mv.MesAgeGroup_id IS NULL)
							)
							and LpuUnitType_id = :LpuUnitType_id
							and MesVol_begDate <= :EvnSection_disDate
							and (MesVol_endDate >= :EvnSection_disDate or MesVol_endDate IS NULL)
					) MESV
					left join v_Diag d (nolock) on d.Diag_id = mod.Diag_id
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id and mt.MesPayType_id = 5 -- Коэффициент КСГ
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
						or (:Person_Age < 8 and mod.MesAgeGroup_id = 7)
						or (:Person_Age >= 8 and mod.MesAgeGroup_id = 8)
						or (:Person_AgeDays <= 90 and mod.MesAgeGroup_id = 9)
						or (mod.MesAgeGroup_id IS NULL)
					)
					and mod.MesOldDiag_begDT <= :EvnSection_disDate
					and (mod.MesOldDiag_endDT >= :EvnSection_disDate OR mod.MesOldDiag_endDT IS NULL)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and (
						mo.Mes_Code NOT IN ('74','75','76','31')
						OR
						exists (select top 1 EvnUsluga_id from v_EvnUsluga eu (nolock) inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id where uc.UslugaComplex_Code in ('A25.30.014', 'A25.30.032') and eu.EvnUsluga_pid = :EvnSection_id and eu.PayType_id = :PayTypeOms_id)
					)
					and (
						d.Diag_Code NOT IN ('O04.0', 'O04.1', 'O04.2', 'O04.3', 'O04.4', 'O04.5', 'O04.6', 'O04.7', 'O04.8', 'O04.9')
						OR
						exists (select top 1 EvnUsluga_id from v_EvnUsluga eu (nolock) inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id where uc.UslugaComplex_Code in ('A16.20.037') and eu.EvnUsluga_pid = :EvnSection_id and eu.PayType_id = :PayTypeOms_id)
					)
				order by
					mt.MesTariff_Value desc
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KSGFromDiag = $resp[0];

					if ( in_array($resp[0]['Diag_Code'], array('I20.1', 'I20.8', 'I20.9', 'I24.0', 'I24.1', 'I24.8', 'I24.9', 'I25.0', 'I25.1', 'I25.2', 'I25.3', 'I25.4', 'I25.5', 'I25.6', 'I25.8', 'I25.9')) ) {
						// проверяем наличие A06.10.006
						$EvnUsluga_id = $this->getFirstResultFromQuery("select top 1 EvnUsluga_id from v_EvnUsluga eu (nolock) inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id where uc.UslugaComplex_Code in ('A06.10.006') and eu.EvnUsluga_pid = :EvnSection_id and eu.PayType_id = :PayTypeOms_id", array('EvnSection_id' => $data['EvnSection_id'], 'PayTypeOms_id' => $data['PayTypeOms_id']));
						// если нашли, то берем ксг по ней 107 (хирургическая)
						if (!empty($EvnUsluga_id)) {
							$query = "
								select top 1
									2 as MesType_id,
									mo.Mes_Code as Mes_Code,
									mo.Mes_Name,
									mo.Mes_id,
									mt.MesTariff_Value as MesTariff_Value,
									mt.MesTariff_id
								from
									v_MesOld mo (nolock)
									cross apply(
										select top 1
											MesVol_id
										from
											r30.v_MesVol mv (nolock)
										where
											mv.Mes_id = mo.Mes_id
											and mv.Lpu_id = :Lpu_id
											and (
												(:Person_Age >= 18 and mv.MesAgeGroup_id = 1)
												or (:Person_Age < 18 and mv.MesAgeGroup_id = 2)
												or (:Person_AgeDays > 28 and mv.MesAgeGroup_id = 3)
												or (:Person_AgeDays <= 28 and mv.MesAgeGroup_id = 4)
												or (:Person_Age < 18 and mv.MesAgeGroup_id = 5)
												or (:Person_Age >= 18 and mv.MesAgeGroup_id = 6)
												or (:Person_Age < 8 and mv.MesAgeGroup_id = 7)
												or (:Person_Age >= 8 and mv.MesAgeGroup_id = 8)
												or (:Person_AgeDays <= 90 and mv.MesAgeGroup_id = 9)
												or (mv.MesAgeGroup_id IS NULL)
											)
											and LpuUnitType_id = :LpuUnitType_id
											and MesVol_begDate <= :EvnSection_disDate
											and (IsNull(MesVol_endDate, :EvnSection_disDate) >= :EvnSection_disDate)
									) MESV
									left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id and mt.MesPayType_id = 5 -- Коэффициент КСГ
								where
									mo.Mes_Code = '107' and mo.MesType_id IN (2,3,5) -- КСГ хирургическая
									and mo.Mes_begDT <= :EvnSection_disDate
									and (IsNull(mo.Mes_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
									and mt.MesTariff_begDT <= :EvnSection_disDate
									and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
									and (exists (
										select top 1
											MesOldUslugaComplex_id
										from
											v_MesOldUslugaComplex muc (nolock)
											inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = muc.UslugaComplex_id
										where
											uc.UslugaComplex_Code = 'A06.10.006'
											and muc.Mes_id = mo.Mes_id
									))
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
						$EvnUsluga_id = $this->getFirstResultFromQuery("select top 1 EvnUsluga_id from v_EvnUsluga eu (nolock) inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id where uc.UslugaComplex_Code in ('A16.12.004.009') and eu.EvnUsluga_pid = :EvnSection_id and eu.PayType_id = :PayTypeOms_id", array('EvnSection_id' => $data['EvnSection_id'], 'PayTypeOms_id' => $data['PayTypeOms_id']));
						// если нашли, то берем ксг по ней 109 (хирургическая)
						if (!empty($EvnUsluga_id)) {
							$query = "
								select top 1
									2 as MesType_id,
									mo.Mes_Code as Mes_Code,
									mo.Mes_Name,
									mo.Mes_id,
									mt.MesTariff_Value as MesTariff_Value,
									mt.MesTariff_id
								from
									v_MesOld mo (nolock)
									cross apply(
										select top 1
											MesVol_id
										from
											r30.v_MesVol mv (nolock)
										where
											mv.Mes_id = mo.Mes_id
											and mv.Lpu_id = :Lpu_id
											and (
												(:Person_Age >= 18 and mv.MesAgeGroup_id = 1)
												or (:Person_Age < 18 and mv.MesAgeGroup_id = 2)
												or (:Person_AgeDays > 28 and mv.MesAgeGroup_id = 3)
												or (:Person_AgeDays <= 28 and mv.MesAgeGroup_id = 4)
												or (:Person_Age < 18 and mv.MesAgeGroup_id = 5)
												or (:Person_Age >= 18 and mv.MesAgeGroup_id = 6)
												or (:Person_Age < 8 and mv.MesAgeGroup_id = 7)
												or (:Person_Age >= 8 and mv.MesAgeGroup_id = 8)
												or (:Person_AgeDays <= 90 and mv.MesAgeGroup_id = 9)
												or (mv.MesAgeGroup_id IS NULL)
											)
											and LpuUnitType_id = :LpuUnitType_id
											and MesVol_begDate <= :EvnSection_disDate
											and (IsNull(MesVol_endDate,  :EvnSection_disDate) >= :EvnSection_disDate)
									) MESV
									left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id and mt.MesPayType_id = 5 -- Коэффициент КСГ
								where
									mo.Mes_Code = '109' and mo.MesType_id IN (2,3,5) -- КСГ хирургическая
									and mo.Mes_begDT <= :EvnSection_disDate
									and (IsNull(mo.Mes_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
									and mt.MesTariff_begDT <= :EvnSection_disDate
									and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
									and (exists (
										select top 1
											MesOldUslugaComplex_id
										from
											v_MesOldUslugaComplex muc (nolock)
											inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = muc.UslugaComplex_id
										where
											uc.UslugaComplex_Code = 'A16.12.004.009'
											and muc.Mes_id = mo.Mes_id
									))
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

					if (!empty($KSGFromDiag)) {
						$combovalues[] = array(
							'MesType_id' => $KSGFromDiag['MesType_id'],
							'Mes_Code' => $KSGFromDiag['Mes_Code'],
							'Mes_Name' => $KSGFromDiag['Mes_Name'],
							'Mes_id' => $KSGFromDiag['Mes_id'],
							'MesTariff_Value' => $KSGFromDiag['MesTariff_Value'],
							'MesTariff_id' => $KSGFromDiag['MesTariff_id']
						);
					}
				}
			}
		}

		// Если для движения не определилось КСГ, то КПГ берём из профиля отделения
		// 3.2	Пробуем определить КПГ по профилю отделения
		if (!empty($data['LpuSectionProfile_id'])) {
			$query = "
				select top 1
					4 as MesType_id,
					mokpg.Mes_Code as Mes_Code,
					mokpg.Mes_Code + ' ' + mokpg.Mes_Name as Mes_Name,
					mokpg.Mes_id,
					mtkpg.MesTariff_Value as MesTariff_Value,
					mtkpg.MesTariff_id
				from MesOld mokpg (nolock)
					left join v_MesTariff mtkpg (nolock) on mtkpg.Mes_id = mokpg.Mes_id and mtkpg.MesPayType_id = 5 -- Коэффициент КПГ
				where
					mokpg.LpuSectionProfile_id = :LpuSectionProfile_id and mokpg.MesType_id IN (4) -- КПГ
					and mokpg.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mokpg.Mes_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mtkpg.MesTariff_begDT <= :EvnSection_disDate
					and (IsNull(mtkpg.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
				order by
					mtkpg.MesTariff_Value desc
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$combovalues[] = $resp[0];
				}
			}
		}

		return $combovalues;
	}

	/**
	 * поиск ксг/кпг/коэф
	 */
	function loadKSGKPGKOEF($data) {
		// 1. загружаем комбо
		$ksgdata = array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'Mes_Name' => null, 'success' => true);
		$combovalues = $this->loadKSGKPGKOEFCombo($data);
		// ищем среди $combovalues MesTariff_id, если нашли возвращаем его.
		$maxMesTariff = array(
			'MesTariff_id' => null,
			'MesOldUslugaComplex_id' => null,
			'MesTariff_Value' => 0
		);
		$maxMesKSGTariff = array(
			'MesTariff_id' => null,
			'MesOldUslugaComplex_id' => null,
			'MesTariff_Value' => 0
		);
		$defaultMesKSGTariff = array(
			'MesTariff_id' => null,
			'MesOldUslugaComplex_id' => null,
			'MesTariff_Value' => 0
		);

		$diag_id = null;
		$evnsection = array();
		if (!empty($data['EvnSection_id'])) {
			$evnsection = $this->getFirstRowFromQuery("select MesTariff_id,Diag_id from v_EvnSection (nolock) where EvnSection_id = :EvnSection_id", $data);
			$diag_id = !empty($evnsection['Diag_id']) ? $evnsection['Diag_id'] : null;
		}
		foreach($combovalues as $combovalue) {
			if (!empty($data['noNeedResetMesTariff'])) {
				if (!empty($data['MesTariff_id']) && $combovalue['MesTariff_id'] == $data['MesTariff_id']) {
					$ksgdata['MesTariff_id'] = $combovalue['MesTariff_id'];
					$ksgdata['MesOldUslugaComplex_id'] = !empty($combovalue['MesOldUslugaComplex_id']) ? $combovalue['MesOldUslugaComplex_id'] : null;
				}
			}
			if ($combovalue['MesType_id'] == 3) {
				$ksgdata['Mes_tid'] = $combovalue['Mes_id'];
			}
			if ($combovalue['MesType_id'] == 2) {
				$ksgdata['Mes_sid'] = $combovalue['Mes_id'];
			}
			if ($combovalue['MesType_id'] == 4) {
				$ksgdata['Mes_kid'] = $combovalue['Mes_id'];
			}

			$ksgdata['Mes_Name'] = $combovalue['Mes_Name'];

			if (empty($maxMesTariff['MesTariff_id']) || $combovalue['MesTariff_Value'] > $maxMesTariff['MesTariff_Value']) {
				$maxMesTariff['MesTariff_id'] = $combovalue['MesTariff_id'];
				$maxMesTariff['MesTariff_Value'] = $combovalue['MesTariff_Value'];
				$maxMesTariff['MesOldUslugaComplex_id'] = !empty($combovalue['MesOldUslugaComplex_id']) ? $combovalue['MesOldUslugaComplex_id'] : null;
			}
			if ($combovalue['MesType_id'] != 4) {
				if (!empty($combovalue['Mes_IsDefault']) && $combovalue['Mes_IsDefault'] == 2) {
					$defaultMesKSGTariff['MesTariff_id'] = $combovalue['MesTariff_id'];
					$defaultMesKSGTariff['MesTariff_Value'] = $combovalue['MesTariff_Value'];
					$defaultMesKSGTariff['MesOldUslugaComplex_id'] = !empty($combovalue['MesOldUslugaComplex_id']) ? $combovalue['MesOldUslugaComplex_id'] : null;
				}
				if (empty($maxMesKSGTariff['MesTariff_id']) || $combovalue['MesTariff_Value'] > $maxMesKSGTariff['MesTariff_Value']) {
					$maxMesKSGTariff['MesTariff_id'] = $combovalue['MesTariff_id'];
					$maxMesKSGTariff['MesTariff_Value'] = $combovalue['MesTariff_Value'];
					$maxMesKSGTariff['MesOldUslugaComplex_id'] = !empty($combovalue['MesOldUslugaComplex_id']) ? $combovalue['MesOldUslugaComplex_id'] : null;
				}
			}
		}
		//Если тариф не был задан или указалии основной диагноз в уже существующем движении
		if (empty($ksgdata['MesTariff_id']) || (empty($diag_id) && !empty($data['Diag_id']) && !empty($data['EvnSection_id']))) {
			if (!empty($defaultMesKSGTariff['MesTariff_id'])) {
				$maxMesTariff['MesTariff_id'] = $defaultMesKSGTariff['MesTariff_id'];
				$maxMesTariff['MesOldUslugaComplex_id'] = $defaultMesKSGTariff['MesOldUslugaComplex_id'];
			} else if (!empty($maxMesKSGTariff['MesTariff_id'])) {
				$maxMesTariff['MesTariff_id'] = $maxMesKSGTariff['MesTariff_id'];
				$maxMesTariff['MesOldUslugaComplex_id'] = $maxMesKSGTariff['MesOldUslugaComplex_id'];
			}
			$ksgdata['MesTariff_id'] = $maxMesTariff['MesTariff_id'];
			$ksgdata['MesOldUslugaComplex_id'] = $maxMesTariff['MesOldUslugaComplex_id'];
		}
		return $ksgdata;
	}
	
	/**
	 * @param $data
	 * @return bool
	 * Мэсы по старому принципу
	 * https://redmine.swan.perm.ru/issues/show/2379
	 */
	function loadMesOldList($data) {
		if (empty($data['forGroup'])) {
			$query = "
				select
					Mes.Mes_id,
					Mes.Mes_Code,
					null as Mes_Name,
					ISNULL(cast(Mes.Mes_KoikoDni as varchar), '') as Mes_KoikoDni,
					'' as MesOperType_Name
				from v_MesOld Mes with (nolock)
				where
					Mes.Diag_id = :Diag_id
			";
		} else {
			// искать мэс для группы диагнозов
			$query = "
				declare @Diag_id bigint = (select top 1 Diag_pid from v_Diag with (nolock) where Diag_id = :Diag_id);
				
				select
					Mes.Mes_id,
					Mes.Mes_Code,
					null as Mes_Name,
					ISNULL(cast(Mes.Mes_KoikoDni as varchar), '') as Mes_KoikoDni,
					'' as MesOperType_Name
				from v_MesOld Mes with (nolock)
				where
					Mes.Diag_id = @Diag_id
			";
		}
        /*
		echo getDebugSql($query, array(
			'Diag_id' => $data['Diag_id']
		)); die();
        */
		$result = $this->db->query($query, array(
			'Diag_id' => $data['Diag_id']
		));

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) < 1 && empty($data['forGroup'])) {
				// надо выбрать мес для группы диагнозов.
				$data['forGroup'] = true;
				return $this->loadMesOldList($data);
			} else {
				return $resp;
			}
		}
		else {
			return false;
		}
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
					es.EvnSection_id
				FROM
					v_EvnSection es with (nolock)
				WHERE
					es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
					and YEAR(ISNULL(es.EvnSection_disDate, es.EvnSection_setDate)) <> :Year
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
	 * Считаем КСКП для движения
	 */
	protected function calcCoeffCTP($data) {
		if (empty($data['PayTypeOms_id'])) {
			$data['PayTypeOms_id'] = $this->getFirstResultFromQuery("select top 1 PayType_id from v_PayType pt with (nolock) where pt.PayType_SysNick = 'oms'");
			if (empty($data['PayTypeOms_id'])) {
				throw new Exception('Ошибка получения идентификатора вида оплаты ОМС', 500);
			}
		}

		$EvnSection_CoeffCTP = 0;
		$EvnSection_TreatmentDiff = "";
		$List = array();

		if (empty($data['EvnSection_setDate'])) {
			$data['EvnSection_setDate'] = date('Y-m-d');
		}

		if (empty($data['EvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['EvnSection_setDate'];
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

		// 1. КС. Сложность лечения пациента, связанная с возрастом (госпитализация детей до 1 года). Кроме КСГ, относящихся к профилю «неонатология»
		$code = '1';
		if (isset($KSLPCodes[$code]) && $data['LpuUnitType_id'] == '1' && $data['Person_Age'] < 1) {
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
				$EvnSection_TreatmentDiff .= $code . ";";
				$coeffCTP = $KSLPCodes[$code];
				$List[] = array(
					'Code' => $code,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 2. КС. Сложность лечения пациента, связанная с возрастом (госпитализация детей от 1 до 4)
		$code = '2';
		if (isset($KSLPCodes[$code]) && $data['LpuUnitType_id'] == '1' && $data['Person_Age'] >= 1 && $data['Person_Age'] < 4) {
			$EvnSection_TreatmentDiff .= $code . ";";
			$coeffCTP = $KSLPCodes[$code];
			$List[] = array(
				'Code' => $code,
				'Value' => $coeffCTP
			);
			if ($EvnSection_CoeffCTP > 0) {
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
			} else {
				$EvnSection_CoeffCTP = $coeffCTP;
			}
		}

		// 3. КС. Необходимость предоставления спального места и питания законному представителю (дети до 4)
		$code = '3';
		if (
			isset($KSLPCodes[$code]) &&
			$data['LpuUnitType_id'] == '1' && (
			($data['EvnSection_IsAdultEscort'] == 2 && $data['Person_Age'] < 4)
			|| ($data['EvnSection_IsMedReason'] == 2 && $data['Person_Age'] >= 4)
		)) {
			$EvnSection_TreatmentDiff .= $code . ";";
			$coeffCTP = $KSLPCodes[$code];
			$List[] = array(
				'Code' => $code,
				'Value' => $coeffCTP
			);
			if ($EvnSection_CoeffCTP > 0) {
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
			} else {
				$EvnSection_CoeffCTP = $coeffCTP;
			}
		}

		$isGeriatr = false;
		if (!empty($data['MesTariff_id'])) {
			$resp_mt = $this->queryResult("
				select top 1
					mt.MesTariff_id
				from
					v_MesTariff mt (nolock)
					inner join v_MesLink ml (nolock) on ml.Mes_id = mt.Mes_id and ml.MesLinkType_id = 1
					inner join v_MesOld mokpg (nolock) on mokpg.Mes_id = ml.Mes_sid
				where
					mt.MesTariff_id = :MesTariff_id
					and mokpg.Mes_Code = '38' -- Гериатрия 
			", array(
				'MesTariff_id' => $data['MesTariff_id']
			));

			if (!empty($resp_mt[0]['MesTariff_id'])) {
				$isGeriatr = true;
			}
		}

		// 4. КС. Сложность лечения пациента, связанная с возрастом (лица старше 75 лет)
		$code = '4';
		if (isset($KSLPCodes[$code]) && $data['LpuUnitType_id'] == '1' && !$isGeriatr && $data['Person_Age'] >= 75) {
			$EvnSection_TreatmentDiff .= $code . ";";
			$coeffCTP = $KSLPCodes[$code];
			$List[] = array(
				'Code' => $code,
				'Value' => $coeffCTP
			);
			if ($EvnSection_CoeffCTP > 0) {
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
			} else {
				$EvnSection_CoeffCTP = $coeffCTP;
			}
		}

		// 5. КС. Необходимость предоставления спального места и питания законному представителю пациента возраста старше 75 лет с индексом Бартела ≤ 60 баллов (для осуществления ухода) при наличии медицинских показаний
		$code = '5';
		if (substr($data['EvnSection_disDate'], 0, 4) >= '2018') {
			if (isset($KSLPCodes[$code]) && $data['LpuUnitType_id'] == '1' && $isGeriatr && $data['Person_Age'] >= 60 && isset($data['EvnSection_BarthelIdx']) && $data['EvnSection_BarthelIdx'] <= 60) {
				$EvnSection_TreatmentDiff .= $code . ";";
				$coeffCTP = $KSLPCodes[$code];
				$List[] = array(
					'Code' => $code,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		} else {
			if (isset($KSLPCodes[$code]) && $data['LpuUnitType_id'] == '1' && $data['EvnSection_IsAdultEscort'] == 2 && $data['EvnSection_IsMedReason'] == 2 && $data['Person_Age'] >= 75) {
				$EvnSection_TreatmentDiff .= $code . ";";
				$coeffCTP = $KSLPCodes[$code];
				$List[] = array(
					'Code' => $code,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 6. КС. Наличие у пациента тяжелой сопутствующей патологии, осложнений заболеваний, сопутствующих заболеваний, влияющих на сложность лечения пациента (перечень указанных заболеваний и состояний представлен в Инструкции)
		$code = '6';
		if (isset($KSLPCodes[$code]) && $data['LpuUnitType_id'] == '1') {
			$AttributeValue = $this->queryResult("
				declare
					@AttributeVision_TablePKey bigint = (select top 1 TariffClass_id from v_TariffClass (nolock) where TariffClass_SysNick = 'diagp');
	
				SELECT top 1
					av.AttributeValue_id
				FROM
					v_AttributeVision avis (nolock)
					inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
					cross apply (
						select top 1
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2 (nolock)
							inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.Diag'
					) DIAGFILTER
					inner join v_EvnDiagPS edps (nolock) on DIAGFILTER.AttributeValue_ValueIdent = edps.Diag_id
				WHERE
					avis.AttributeVision_TableName = 'dbo.TariffClass'
					and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
					and avis.AttributeVision_IsKeyValue = 2
					and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
					and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
					and edps.DiagSetClass_id in (3) -- обязательно только сопутствующий
					and edps.EvnDiagPS_pid = :EvnSection_id
			", array(
				'EvnSection_disDate' => $data['EvnSection_disDate'],
				'EvnSection_id' => $data['EvnSection_id'],
			));
			if (!empty($AttributeValue[0]['AttributeValue_id'])) {
				$EvnSection_TreatmentDiff .= "6;";
				$coeffCTP = $KSLPCodes[$code];
				$List[] = array(
					'Code' => $code,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 7. КС. Необходимость развертывания индивидуального поста по медицинским показаниям
		$code = '7';
		if (isset($KSLPCodes[$code]) && $data['LpuUnitType_id'] == '1' && false) { // по ТЗ это пока не учитываем
			$EvnSection_TreatmentDiff .= $code . ";";
			$coeffCTP = $KSLPCodes[$code];
			$List[] = array(
				'Code' => $code,
				'Value' => $coeffCTP
			);
			if ($EvnSection_CoeffCTP > 0) {
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
			} else {
				$EvnSection_CoeffCTP = $coeffCTP;
			}
		}

		if (substr($data['EvnSection_disDate'], 0, 4) >= '2018') {
			// для 2018 такого нет
		} else {
			// 8. КС. Необходимость предоставления спального места и питания законному представителю ребенка после достижения им возраста 4 лет при наличии медицинских показаний
			$code = '8';
			if (isset($KSLPCodes[$code]) && $data['LpuUnitType_id'] == '1' && $data['EvnSection_IsAdultEscort'] == 2 && $data['EvnSection_IsMedReason'] == 2 && $data['Person_Age'] >= 4 && $data['Person_Age'] < 18) {
				$EvnSection_TreatmentDiff .= $code . ";";
				$coeffCTP = $KSLPCodes[$code];
				$List[] = array(
					'Code' => $code,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 8. КС и ДС (с 2018). Проведение в рамках одной госпитализации в полном объеме нескольких видов противоопухолевого лечения, относящихся к разным КСГ (перечень возможных сочетаний КСГ представлен в Инструкции)
		$code = '8';
		if (substr($data['EvnSection_disDate'], 0, 4) < '2018') {
			$code = '9';
		}
		$needKSLP8 = false;
		if (isset($KSLPCodes[$code]) && (empty($data['CureResult_id']) || $data['CureResult_id'] != 2) && ($data['LpuUnitType_id'] == '1' || substr($data['EvnSection_disDate'], 0, 4) >= '2018')) {
			$mesTypeFilter = "and mo.MesType_id IN (10,14)";
			if (in_array($data['LpuUnitType_id'], array('6', '7', '9'))) {
				$mesTypeFilter = "and mo.MesType_id IN (9,13)";
			}

			// Услуга с атрибутом «Лучевая терапия» + Услуга, введенная через форму «Добавление операции»;
			$EvnUsluga = $this->queryResult("
				with eus as (
					select
						eu.EvnUsluga_id,
						eu.UslugaComplex_id,
						mo.Mes_id,
						eu.EvnClass_id
					FROM
						v_EvnUsluga eu (nolock)
						inner join v_MesOldUslugaComplex mouc (nolock) on mouc.UslugaComplex_id = eu.UslugaComplex_id and ISNULL(mouc.MesOldUslugaComplex_begDT, :EvnSection_disDate) <= :EvnSection_disDate and ISNULL(mouc.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate
						inner join v_MesOld mo (nolock) on mo.Mes_id = mouc.Mes_id {$mesTypeFilter}
					where
						eu.EvnUsluga_pid = :EvnSection_id
						and eu.EvnClass_id in (43,22,29)
						and eu.PayType_id = :PayTypeOms_id
				)
				
				SELECT top 1
					eu.EvnUsluga_id
				FROM
					eus eu (nolock)
					inner join v_UslugaComplexAttribute uca (nolock) on uca.UslugaComplex_id = eu.UslugaComplex_id and ISNULL(uca.UslugaComplexAttribute_begDate, :EvnSection_disDate) <= :EvnSection_disDate and ISNULL(uca.UslugaComplexAttribute_endDate, :EvnSection_disDate) >= :EvnSection_disDate
				WHERE
					uca.UslugaComplexAttributeType_id = :UslugaComplexAttributeType_id
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
				'PayTypeOms_id' => $data['PayTypeOms_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate'],
				'EvnSection_id' => $data['EvnSection_id'],
				'UslugaComplexAttributeType_id' => $this->getUslugaComplexAttributeTypeIdBySysNick('luchter')
			));

			if (!empty($EvnUsluga[0]['EvnUsluga_id'])) {
				$needKSLP8 = true;
			}

			if (!$needKSLP8) {
				// наличие в случае схем лекарственной терапии, в том числе одинаковых
				$resp_esdts = $this->queryResult("
					with dts as (
						select
							esdts.EvnSectionDrugTherapyScheme_id,
							esdts.DrugTherapyScheme_id,
							mo.Mes_id
						FROM
							v_EvnSectionDrugTherapyScheme esdts (nolock)
							inner join v_DrugTherapyScheme dts (nolock) on dts.DrugTherapyScheme_id = esdts.DrugTherapyScheme_id and dts.DrugTherapyScheme_Code like 'sh%'
							inner join v_MesOldUslugaComplex mouc (nolock) on mouc.DrugTherapyScheme_id = esdts.DrugTherapyScheme_id and ISNULL(mouc.MesOldUslugaComplex_begDT, :EvnSection_disDate) <= :EvnSection_disDate and ISNULL(mouc.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate
							inner join v_MesOld mo (nolock) on mo.Mes_id = mouc.Mes_id {$mesTypeFilter}
						where
							esdts.EvnSection_id = :EvnSection_id
					)
					
					SELECT top 1
						esdts.EvnSectionDrugTherapyScheme_id,
						esdts2.EvnSectionDrugTherapyScheme_id as EvnSectionDrugTherapyScheme2_id,
						esdts.Mes_id
					FROM
						dts esdts (nolock)
						outer apply (
							select top 1
								esdts2.EvnSectionDrugTherapyScheme_id
							from
								dts esdts2 (nolock)
							where
								esdts2.EvnSectionDrugTherapyScheme_id <> esdts.EvnSectionDrugTherapyScheme_id
								and esdts2.Mes_id <> esdts.Mes_id -- относятся к разным КСГ
						) esdts2
				", array(
					'EvnSection_disDate' => $data['EvnSection_disDate'],
					'EvnSection_id' => $data['EvnSection_id']
				));

				if (!empty($resp_esdts[0]['EvnSectionDrugTherapyScheme2_id'])) {
					$needKSLP8 = true;
				} else if (!empty($resp_esdts[0]['Mes_id'])) {
					// схема + услуга заведенная через форму "Добавление операции" или с атрибутом лучевая терапия
					$EvnUsluga = $this->queryResult("
						select top 1
							eu.EvnUsluga_id
						FROM
							v_EvnUsluga eu (nolock)
							inner join v_MesOldUslugaComplex mouc (nolock) on mouc.UslugaComplex_id = eu.UslugaComplex_id and ISNULL(mouc.MesOldUslugaComplex_begDT, :EvnSection_disDate) <= :EvnSection_disDate and ISNULL(mouc.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate
							inner join v_MesOld mo (nolock) on mo.Mes_id = mouc.Mes_id {$mesTypeFilter}
						where
							eu.EvnUsluga_pid = :EvnSection_id
							and eu.EvnClass_id in (43)
							and eu.PayType_id = :PayTypeOms_id
							and mo.Mes_id <> :Mes_id
						
						union all
						
						select top 1
							eu.EvnUsluga_id
						FROM
							v_EvnUsluga eu (nolock)
							inner join v_MesOldUslugaComplex mouc (nolock) on mouc.UslugaComplex_id = eu.UslugaComplex_id and ISNULL(mouc.MesOldUslugaComplex_begDT, :EvnSection_disDate) <= :EvnSection_disDate and ISNULL(mouc.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate
							inner join v_MesOld mo (nolock) on mo.Mes_id = mouc.Mes_id {$mesTypeFilter}
							inner join v_UslugaComplexAttribute uca (nolock) on uca.UslugaComplex_id = eu.UslugaComplex_id and ISNULL(uca.UslugaComplexAttribute_begDate, :EvnSection_disDate) <= :EvnSection_disDate and ISNULL(uca.UslugaComplexAttribute_endDate, :EvnSection_disDate) >= :EvnSection_disDate
							inner join v_UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
						where
							eu.EvnUsluga_pid = :EvnSection_id
							and eu.EvnClass_id in (43,22,29)
							and eu.PayType_id = :PayTypeOms_id
							and ucat.UslugaComplexAttributeType_SysNick IN ('luchter')
							and mo.Mes_id <> :Mes_id
					", array(
						'PayTypeOms_id' => $data['PayTypeOms_id'],
						'EvnSection_disDate' => $data['EvnSection_disDate'],
						'EvnSection_id' => $data['EvnSection_id'],
						'Mes_id' => $resp_esdts[0]['Mes_id']
					));

					if (!empty($EvnUsluga[0]['EvnUsluga_id'])) {
						$needKSLP8 = true;
					}
				}
			}

			if ($needKSLP8) {
				$EvnSection_TreatmentDiff .= $code . ";";
				$coeffCTP = $KSLPCodes[$code];
				$List[] = array(
					'Code' => $code,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 10. КС. Проведение сочетанных хирургических вмешательств (перечень возможных сочетанных операций представлен в Инструкции)
		$code = '10';
		if (substr($data['EvnSection_disDate'], 0, 4) < '2018') {
			$code = '11';
		}
		if (isset($KSLPCodes[$code]) && $data['LpuUnitType_id'] == '1') {
			$queryResult = $this->queryResult("
				declare
					@AttributeVision_TablePKey bigint = (select top 1 TariffClass_id from v_TariffClass (nolock) where TariffClass_SysNick = 'SochHirVmesh');
	
				with UCList (
					UslugaComplex_id
				) as (
					select eu.UslugaComplex_id
					from v_EvnUsluga eu (nolock)
					where eu.EvnUsluga_pid = :EvnSection_id
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
							and av2.AttributeValue_ValueIdent in (select UslugaComplex_id from UCList)
					) UC1FILTER
					cross apply (
						select
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2 (nolock)
							inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.UslugaComplex'
							and av2.AttributeValue_ValueIdent <> UC1FILTER.AttributeValue_ValueIdent
							and av2.AttributeValue_ValueIdent in (select UslugaComplex_id from UCList)
					) UC2FILTER
				WHERE
					avis.AttributeVision_TableName = 'dbo.TariffClass'
					and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
					and avis.AttributeVision_IsKeyValue = 2
					and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
					and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
			", array(
				'EvnSection_disDate' => $data['EvnSection_disDate'],
				'EvnSection_id' => $data['EvnSection_id'],
				'PayTypeOms_id' => $data['PayTypeOms_id']
			));
			if (is_array($queryResult) && count($queryResult) > 0 && !empty($queryResult[0]['AttributeValue_id'])) {
				$EvnSection_TreatmentDiff .= $code . ";";
				$coeffCTP = $KSLPCodes[$code];
				$List[] = array(
					'Code' => $code,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 11. КС. Проведение однотипных операций на парных органах (перечень возможных однотипных операций на парных органах представлен в Инструкции)
		$code = '11';
		if (substr($data['EvnSection_disDate'], 0, 4) < '2018') {
			$code = '12';
		}
		if (isset($KSLPCodes[$code]) && $data['LpuUnitType_id'] == '1') {
			$EvnUsluga = $this->queryResult("
				SELECT
					SUM(ISNULL(eu.EvnUsluga_Kolvo, 1)) as sum
				FROM
					v_EvnUsluga eu (nolock)
				WHERE
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnClass_id in (43,22,29)
					and exists(
						select top 1
							uca.UslugaComplexAttribute_id
						from
							v_UslugaComplexAttribute uca (nolock)
							inner join v_UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
						where
							uca.UslugaComplex_id = eu.UslugaComplex_id
							and ucat.UslugaComplexAttributeType_SysNick = 'parorg'
				  			and ISNULL(uca.UslugaComplexAttribute_begDate, :EvnSection_disDate) <= :EvnSection_disDate
							and ISNULL(uca.UslugaComplexAttribute_endDate, :EvnSection_disDate) >= :EvnSection_disDate
					)
			", array(
				'PayTypeOms_id' => $data['PayTypeOms_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate'],
				'EvnSection_id' => $data['EvnSection_id'],
			));
			if (!empty($EvnUsluga[0]['sum']) && $EvnUsluga[0]['sum'] > 1) {
				$EvnSection_TreatmentDiff .= $code . ";";
				$coeffCTP = $KSLPCodes[$code];
				$List[] = array(
					'Code' => $code,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 11.1. КС. Одномоментное проведение ангиографических исследований разных сосудистых бассейнов кровеносного русла, в том числе сочетание коронарографии и ангиографии (#136745)
		$code = '11.1';
		if (isset($KSLPCodes[$code]) && $data['LpuUnitType_id'] == '1') {
			$EvnUsluga = $this->queryResult("
				SELECT
					SUM(ISNULL(eu.EvnUsluga_Kolvo, 1)) as sum
				FROM
					v_EvnUsluga eu (nolock)
				WHERE
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnClass_id in (43,22,29)
					and exists(
						select top 1
							uca.UslugaComplexAttribute_id
						from
							v_UslugaComplexAttribute uca (nolock)
							inner join v_UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
						where
							uca.UslugaComplex_id = eu.UslugaComplex_id
							and ucat.UslugaComplexAttributeType_SysNick = 'AngiografIsl'
				  			and ISNULL(uca.UslugaComplexAttribute_begDate, :EvnSection_disDate) <= :EvnSection_disDate
							and ISNULL(uca.UslugaComplexAttribute_endDate, :EvnSection_disDate) >= :EvnSection_disDate
					)
			", array(
				'PayTypeOms_id' => $data['PayTypeOms_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate'],
				'EvnSection_id' => $data['EvnSection_id'],
			));
			if (!empty($EvnUsluga[0]['sum']) && $EvnUsluga[0]['sum'] > 1) {
				$EvnSection_TreatmentDiff .= $code . ";";
				$coeffCTP = $KSLPCodes[$code];
				$List[] = array(
					'Code' => $code,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 18. КС. Коронавирусная инфекция, вызванная вирусом COVID-19:
		//вирус идентифицирован (подтвержден лабораторным тестированием независимо от тяжести клинических признаков или симптомов U07.1;
		//вирус не идентифицирован (COVID-19 диагностируется клинически или эпидемиологически, но лабораторные исследования неубедительны или недоступны) U07.2
		$code = '18';
		if (isset($KSLPCodes[$code]) && $data['LpuUnitType_id'] == '1') {

			$Diag_id = $this->getFirstResultFromQuery("
				select
					es.Diag_id
				from
					v_EvnSection es (nolock) 
					left join v_Diag d with (nolock) on d.Diag_id = es.Diag_id
				where
					EvnSection_id = :EvnSection_id
					and d.Diag_Code in ('U07.1', 'U07.2')
			", array(
				'EvnSection_id' => $data['EvnSection_id']
			));
			
			if ($Diag_id) {
				$EvnSection_TreatmentDiff .= $code . ";";
				$coeffCTP = $KSLPCodes[$code];
				$List[] = array(
					'Code' => $code,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}
		
		// 12. КС Проведение антимикробной терапии инфекций, вызванных полирезистентными микроорганизмами
		$code = '12';
		if (isset($KSLPCodes[$code]) && $data['LpuUnitType_id'] == '1') {
			
			$Diag_id = $this->getFirstResultFromQuery("
					select
						top 1 d.Diag_id
					from v_EvnSection es (nolock)
					left join v_Diag d (nolock) on d.Diag_id = es.Diag_id
					where es.EvnSection_id IN ('" . implode("','", $data['EvnSectionIds']) . "')
						and (
							d.Diag_Code between 'A00' and 'B99'
							or exists(
							select top 1
								edps.EvnDiagPS_id
							from
								v_EvnDiagPS edps (nolock)
								inner join v_Diag diag (nolock) on diag.Diag_id = edps.Diag_id
							where
								edps.DiagSetClass_id IN (2)
								and edps.EvnDiagPS_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
								and diag.Diag_Code between 'A00' and 'B99'
							)
						)
				");
			
			$EvnUsluga_id = $this->getFirstResultFromQuery("
					SELECT top 1
						eu.EvnUsluga_id
					FROM
						v_EvnUsluga eu (nolock)
						inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					WHERE
						eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
						and eu.EvnClass_id in (43,22,29)
						and eu.EvnUsluga_setDT is not null
						and left(uc.UslugaComplex_Code, 6) = 'A25.30'
				");
			
			$Drug_id = $this->getFirstResultFromQuery("
				SELECT
					Drug.Drug_id
				FROM
					rls.v_Drug Drug with (nolock)
					left join v_EvnCourseTreatDrug ec_drug with (nolock) on Drug.Drug_id = ec_drug.Drug_id
					left join v_EvnCourseTreat ECT  with (nolock) on ECT.EvnCourseTreat_id = ec_drug.EvnCourseTreat_id
				WHERE
					ECT.EvnCourseTreat_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
					and DrugMnn_id in ('3005','6224','5474','6744','6744','6806','6810','5510','1996','3808','2418','1794',
					'1355','1115','574','3217','3188','5506','87','3506','5699','6034')
					and ECT.EvnCourseTreat_Duration > 4
			");
			
			if ($data['Duration'] > 4 && !empty($Diag_id) && !empty($EvnUsluga_id) && !empty($Drug_id)) {
				$EvnSection_TreatmentDiff .= $code . ";";
				$coeffCTP = $KSLPCodes[$code];
				$List[] = array(
					'Code' => $code,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		if (substr($data['EvnSection_disDate'], 0, 4) < '2018') {
			// до 2018 такого нет
		} else {
			// 15. ДС. Проведение первого этапа экстракорпорального оплодотворения (стимуляция суперовуляции) (#136745)
			$code = '15';
			if (isset($KSLPCodes[$code]) && in_array($data['LpuUnitType_id'], array('6','7','9'))) {
				$EvnUsluga = $this->queryResult("
					SELECT top 1
						eu.EvnUsluga_id
					FROM
						v_EvnUsluga eu (nolock)
						inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
						inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
					WHERE
						eu.EvnUsluga_pid = :EvnSection_id
						and eu.PayType_id = :PayTypeOms_id
						and eu.EvnClass_id in (43,22,29)
						and uc.UslugaComplex_Code = 'A11.20.017.001'
						and ucat.UslugaCategory_SysNick = 'tfoms'
						and not exists(
							SELECT top 1
								eu2.EvnUsluga_id
							FROM
								v_EvnUsluga eu2 (nolock)
								inner join v_UslugaComplex uc2 (nolock) on uc2.UslugaComplex_id = eu2.UslugaComplex_id
								inner join v_UslugaCategory ucat2 (nolock) on ucat2.UslugaCategory_id = uc2.UslugaCategory_id
							WHERE
								eu2.EvnUsluga_pid = :EvnSection_id
								and eu2.PayType_id = :PayTypeOms_id
								and eu2.EvnClass_id in (43,22,29)
								and uc2.UslugaComplex_Code = 'A11.20.017.002'
								and ucat2.UslugaCategory_SysNick = 'tfoms'
						)
				", array(
					'PayTypeOms_id' => $data['PayTypeOms_id'],
					'EvnSection_disDate' => $data['EvnSection_disDate'],
					'EvnSection_id' => $data['EvnSection_id'],
				));
				if (!empty($EvnUsluga[0]['EvnUsluga_id'])) {
					$EvnSection_TreatmentDiff .= $code . ";";
					$coeffCTP = $KSLPCodes[$code];
					$List[] = array(
						'Code' => $code,
						'Value' => $coeffCTP
					);
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
				}
			}

			// 16. ДС. Полный цикл экстракорпорального оплодотворения с криоконсервацией эмбрионов (#136745)
			$code = '16';
			if (isset($KSLPCodes[$code]) && in_array($data['LpuUnitType_id'], array('6','7','9'))) {
				$EvnUsluga = $this->queryResult("
					SELECT top 1
						eu.EvnUsluga_id
					FROM
						v_EvnUsluga eu (nolock)
						inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
						inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
					WHERE
						eu.EvnUsluga_pid = :EvnSection_id
						and eu.PayType_id = :PayTypeOms_id
						and eu.EvnClass_id in (43,22,29)
						and uc.UslugaComplex_Code = 'A11.20.017.002'
						and ucat.UslugaCategory_SysNick = 'tfoms'
				", array(
					'PayTypeOms_id' => $data['PayTypeOms_id'],
					'EvnSection_disDate' => $data['EvnSection_disDate'],
					'EvnSection_id' => $data['EvnSection_id'],
				));
				if (!empty($EvnUsluga[0]['EvnUsluga_id'])) {
					$EvnSection_TreatmentDiff .= $code . ";";
					$coeffCTP = $KSLPCodes[$code];
					$List[] = array(
						'Code' => $code,
						'Value' => $coeffCTP
					);
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
				}
			}

			// 17. ДС. Размораживание криоконсервированных эмбрионов с последующим переносом эмбрионов в полость матки (неполный цикл) (#136745)
			$code = '17';
			if (isset($KSLPCodes[$code]) && in_array($data['LpuUnitType_id'], array('6','7','9'))) {
				$EvnUsluga = $this->queryResult("
					SELECT top 1
						eu.EvnUsluga_id
					FROM
						v_EvnUsluga eu (nolock)
						inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
						inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
					WHERE
						eu.EvnUsluga_pid = :EvnSection_id
						and eu.PayType_id = :PayTypeOms_id
						and eu.EvnClass_id in (43,22,29)
						and uc.UslugaComplex_Code = 'A11.20.030.001'
						and ucat.UslugaCategory_SysNick = 'gost2011'
						and not exists(
							SELECT top 1
								eu2.EvnUsluga_id
							FROM
								v_EvnUsluga eu2 (nolock)
								inner join v_UslugaComplex uc2 (nolock) on uc2.UslugaComplex_id = eu2.UslugaComplex_id
								inner join v_UslugaCategory ucat2 (nolock) on ucat2.UslugaCategory_id = uc2.UslugaCategory_id
							WHERE
								eu2.EvnUsluga_pid = :EvnSection_id
								and eu2.PayType_id = :PayTypeOms_id
								and eu2.EvnClass_id in (43,22,29)
								and uc2.UslugaComplex_Code IN ('A11.20.017.001', 'A11.20.017.002')
								and ucat2.UslugaCategory_SysNick = 'tfoms'
						)
				", array(
					'PayTypeOms_id' => $data['PayTypeOms_id'],
					'EvnSection_disDate' => $data['EvnSection_disDate'],
					'EvnSection_id' => $data['EvnSection_id'],
				));
				if (!empty($EvnUsluga[0]['EvnUsluga_id'])) {
					$EvnSection_TreatmentDiff .= $code . ";";
					$coeffCTP = $KSLPCodes[$code];
					$List[] = array(
						'Code' => $code,
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

		if (empty($EvnSection_TreatmentDiff)) {
			$EvnSection_TreatmentDiff = null;
		}

		// КСЛП (без КСЛП по длительности) не может превышать 1.8
		if ($EvnSection_CoeffCTP > 1.8) {
			$EvnSection_CoeffCTP = 1.8;
		}

		// 10. КС. Сверхдлительные сроки госпитализации, обусловленные медицинскими показаниями
		$code = '9';
		if (substr($data['EvnSection_disDate'], 0, 4) < '2018') {
			$code = '10';
		}

		$checkMes = $data['Mes_Code'];
		$ksgExcArray = array();
		if (substr($data['EvnSection_disDate'], 0, 4) >= '2019') {
			$checkMes = $data['MesOld_Num'];
			$ksg45Array = array('st10.001', 'st10.002', 'st17.002', 'st17.003', 'st29.007', 'st32.006', 'st32.007', 'st33.007');
			// КСЛП НЕ применяется. Если КСГ st19.039-st19.055
			$ksgExcArray = array('st19.039', 'st19.040', 'st19.041', 'st19.042', 'st19.043', 'st19.044', 'st19.045', 'st19.046', 'st19.047', 'st19.048', 'st19.049', 'st19.050', 'st19.051', 'st19.052', 'st19.053', 'st19.054', 'st19.055');
		} else if (substr($data['EvnSection_disDate'], 0, 4) >= '2018') {
			$ksg45Array = array('45', '46', '108', '109', '161', '162', '233', '279', '280', '298');
		} else {
			$ksg45Array = array('44', '45', '106', '107', '148', '149', '220', '266', '267', '285');
		}
		if ($data['LpuUnitType_id'] == '1') {
			if (
				!in_array($checkMes, $ksgExcArray)
				&& (
					($data['Duration'] > 30 && !in_array($checkMes, $ksg45Array))
					|| $data['Duration'] > 45
				)
			) {
				$EvnSection_TreatmentDiff .= $code . ";";
				$normDays = 30;
				if (in_array($checkMes, $ksg45Array)) {
					$normDays = 45;
				}

				$coefDl = 0.25;
				$reanimProfiles = array('5', '167', '300');
				// для реанимационных 0,4
				if (count(array_intersect($reanimProfiles, $data['LpuSectionProfileCodes'])) > 0) {
					$coefDl = 0.4;
				} else if (!empty($data['EvnSectionIds'])) {
					$EvnUsluga = $this->queryResult("
						SELECT top 1
							eu.EvnUsluga_id
						FROM
							v_EvnUsluga eu (nolock)
							inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
							inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
						WHERE
							eu.EvnUsluga_pid in (" . implode(',', $data['EvnSectionIds']) . ")
							and eu.PayType_id = :PayTypeOms_id
							and eu.EvnClass_id in (43,22,29)
							and uc.UslugaComplex_Code = 'B03.003.005'
							and ucat.UslugaCategory_SysNick = 'gost2011'
					", array(
						'PayTypeOms_id' => $data['PayTypeOms_id']
					));

					if (!empty($EvnUsluga[0]['EvnUsluga_id'])) {
						$coefDl = 0.4;
					}
				}

				$coeffCTP = 1 + ($data['Duration'] - $normDays) * $coefDl / $normDays;

				// округляем полученный коэффициент до 2 знаков после запятой
				$coeffCTP = round($coeffCTP, 2);

				$List[] = array(
					'Code' => $code,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		$EvnSection_CoeffCTP = round($EvnSection_CoeffCTP, 3);

		return array(
			'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP,
			'EvnSection_TreatmentDiff' => $EvnSection_TreatmentDiff,
			'List' => $List
		);
	}

	/**
	 * Пересчёт КСКП для всей КВС
	 */
	protected function _recalcKSKP()
	{
		// убираем признаки со всех движений КВС
		$query = "
			update
				es with (rowlock)
			set
				es.EvnSection_CoeffCTP = null,
				es.EvnSection_TreatmentDiff = null
			from
				EvnSection es
				inner join Evn e (nolock) on e.Evn_id = es.EvnSection_id
			where
				e.Evn_pid = :EvnSection_pid
		";
		$this->db->query($query, array(
			'EvnSection_pid' => $this->pid
		));

		// удаляем все связки КСЛП по всем движениям.
		$query = "
			select
				eskl.EvnSectionKSLPLink_id
			from
				EvnSectionKSLPLink eskl (nolock)
				inner join Evn e (nolock) on e.Evn_id = eskl.EvnSection_id
			where
				e.Evn_pid = :EvnSection_pid
		";
		$resp_eskl = $this->queryResult($query, array(
			'EvnSection_pid' => $this->pid
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

		$groupped = array();
		// достаём группы и для каждой определяем EvnSection_CoeffCTP / EvnSection_TreatmentDiff и апдейтим движения
		$resp_es = $this->queryResult("
			select
				es.EvnSection_id,
				dbo.Age2(PS.Person_BirthDay, es.EvnSection_setDate) as Person_Age,
				es.EvnSection_setDT,
				convert(varchar(10), es.EvnSection_setDate, 120) as EvnSection_setDate,
				convert(varchar(10), ISNULL(es.EvnSection_disDate, es.EvnSection_setDate), 120) as EvnSection_disDate,
				lu.LpuUnitType_id,
				ls.LpuSectionProfile_Code,
				es.EvnSection_BarthelIdx,
				es.EvnSection_IndexNum,
				es.Lpu_id,
				mt.MesTariff_Value,
				es.MesTariff_id,
				mo.Mes_Code,
				mo.MesOld_Num,
				es.EvnSection_IsAdultEscort,
				es.EvnSection_IsMedReason,
				es.CureResult_id,
				pt.PayType_SysNick
			from
				v_EvnSection es with (nolock)
				inner join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
				inner join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
				left join v_PayType pt (nolock) on pt.PayType_id = es.PayType_id
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
				left join v_PersonState ps (nolock) on ps.Person_id = es.Person_id
				left join v_MesTariff mt (nolock) on mt.MesTariff_id = es.MesTariff_id
				left join v_MesOld mo (nolock) on mo.Mes_id = mt.Mes_id
			where
				es.EvnSection_pid = :EvnSection_pid
		", array(
			'EvnSection_pid' => $this->pid
		));

		$k = 0;
		foreach($resp_es as $respone) {
			$key = $respone['EvnSection_IndexNum'];

			if (empty($key)) {
				continue;
			}

			$respone['EvnSection_CoeffCTP'] = 1;
			$respone['EvnSection_TreatmentDiff'] = null;
			$groupped[$key]['EvnSections'][$respone['EvnSection_id']] = $respone;
			$groupped[$key]['MaxCoeff']['Lpu_id'] = $respone['Lpu_id'];

			// Возраст человека берём из первого движения группы, т.е. минимальный
			if (!isset($groupped[$key]['MaxCoeff']['Person_Age']) || $groupped[$key]['MaxCoeff']['Person_Age'] > $respone['Person_Age']) {
				$groupped[$key]['MaxCoeff']['Person_Age'] = $respone['Person_Age'];
			}

			// Дату начала движений из первого движения
			if (empty($groupped[$key]['MaxCoeff']['EvnSection_setDate']) || strtotime($groupped[$key]['MaxCoeff']['EvnSection_setDate']) > strtotime($respone['EvnSection_setDate'])) {
				$groupped[$key]['MaxCoeff']['EvnSection_setDate'] = $respone['EvnSection_setDate'];
			}

			// Дату окончания движений из последнего движения
			if (empty($groupped[$key]['MaxCoeff']['EvnSection_disDate']) || strtotime($groupped[$key]['MaxCoeff']['EvnSection_disDate']) < strtotime($respone['EvnSection_disDate'])) {
				$groupped[$key]['MaxCoeff']['EvnSection_disDate'] = $respone['EvnSection_disDate'];
			}

			// если есть хотя бы на одном из группы
			if (empty($groupped[$key]['MaxCoeff']['EvnSection_IsAdultEscort']) || $respone['EvnSection_IsAdultEscort'] == 2) {
				$groupped[$key]['MaxCoeff']['EvnSection_IsAdultEscort'] = $respone['EvnSection_IsAdultEscort'];
			}

			// если есть хотя бы на одном из группы
			if (empty($groupped[$key]['MaxCoeff']['EvnSection_IsMedReason']) || $respone['EvnSection_IsMedReason'] == 2) {
				$groupped[$key]['MaxCoeff']['EvnSection_IsMedReason'] = $respone['EvnSection_IsMedReason'];
			}

			if (empty($groupped[$key]['MaxCoeff']['DurationSec'])) {
				$groupped[$key]['MaxCoeff']['DurationSec'] = 0;
			}
			// Длительность - общая длительность групы, только движений по ОМС
			if ($respone['PayType_SysNick'] == 'oms' && !empty($respone['EvnSection_setDate']) && !empty($respone['EvnSection_disDate'])) {
				$datediff = strtotime($respone['EvnSection_disDate']) - strtotime($respone['EvnSection_setDate']);
				$groupped[$key]['MaxCoeff']['DurationSec'] += $datediff;
			}

			// КСГ с движения с наибольшим коэффициентом / если коэфф тот же, то с наибольшей датой начала
			if (
				empty($groupped[$key]['MaxCoeff']['MesTariff_Value'])
				|| $groupped[$key]['MaxCoeff']['MesTariff_Value'] < $respone['MesTariff_Value']
				|| ($groupped[$key]['MaxCoeff']['MesTariff_Value'] == $respone['MesTariff_Value'] && $groupped[$key]['MaxCoeff']['EvnSection_setDate'] < $respone['EvnSection_setDate'])
			) {
				$groupped[$key]['MaxCoeff']['MesTariff_Value'] = $respone['MesTariff_Value'];
				$groupped[$key]['MaxCoeff']['LpuSectionProfile_Code'] = $respone['LpuSectionProfile_Code'];
				$groupped[$key]['MaxCoeff']['EvnSection_BarthelIdx'] = $respone['EvnSection_BarthelIdx'];
				$groupped[$key]['MaxCoeff']['LpuUnitType_id'] = $respone['LpuUnitType_id'];
				$groupped[$key]['MaxCoeff']['MesTariff_id'] = $respone['MesTariff_id'];
				$groupped[$key]['MaxCoeff']['Mes_Code'] = $respone['Mes_Code'];
				$groupped[$key]['MaxCoeff']['MesOld_Num'] = $respone['MesOld_Num'];
			}
		}

		// для каждого движения группы надо выбрать движение с наибольшим КСГ.
		foreach($groupped as $key => $group) {
			$EvnSectionIds = array();
			foreach($group['EvnSections'] as $es) {
				$EvnSectionIds[] = $es['EvnSection_id'];
			}
			$groupped[$key]['MaxCoeff']['EvnSectionIds'] = $EvnSectionIds; // все джвижения группы

			$groupped[$key]['MaxCoeff']['Duration'] = floor($groupped[$key]['MaxCoeff']['DurationSec'] / (60 * 60 * 24));
			if ($groupped[$key]['MaxCoeff']['Duration'] == 0) {
				$groupped[$key]['MaxCoeff']['Duration'] = 1;
			}
		}

		foreach($groupped as $group) {
			// Собираем идентификаторы и профили группы движений
			$EvnSectionIds = array();
			$LpuSectionProfileCodes = array();
			foreach($group['EvnSections'] as $es) {
				$EvnSectionIds[] = $es['EvnSection_id'];
				$LpuSectionProfileCodes[] = $es['LpuSectionProfile_Code'];
			}

			// 4. записываем для каждого движения группы полученные КСЛП в БД.
			foreach($group['EvnSections'] as $es) {
				$esdata = array(
					'EvnSection_id' => $es['EvnSection_id'],
					'EvnSectionIds' => $EvnSectionIds,
					'LpuSectionProfileCodes' => $LpuSectionProfileCodes,
					'LpuSectionProfile_Code' => $es['LpuSectionProfile_Code'],
					'EvnSection_BarthelIdx' => $es['EvnSection_BarthelIdx'],
					'LpuUnitType_id' => $es['LpuUnitType_id'],
					'EvnSection_disDate' => $group['MaxCoeff']['EvnSection_disDate'],
					'Person_Age' => $group['MaxCoeff']['Person_Age'],
					'Duration' => $group['MaxCoeff']['Duration'],
					'MesTariff_id' => $es['MesTariff_id'],
					'Mes_Code' => $es['Mes_Code'],
					'MesOld_Num' => $es['MesOld_Num'],
					'EvnSection_IsAdultEscort' => $es['EvnSection_IsAdultEscort'],
					'EvnSection_IsMedReason' => $es['EvnSection_IsMedReason'],
					'CureResult_id' => $es['CureResult_id'],
					'EvnSection_setDate' => $group['MaxCoeff']['EvnSection_setDate']
				);

				$kslp = $this->calcCoeffCTP($esdata);

				$query = "
					update
						EvnSection with (rowlock)
					set
						EvnSection_CoeffCTP = :EvnSection_CoeffCTP,
						EvnSection_TreatmentDiff = :EvnSection_TreatmentDiff
					where
						EvnSection_id = :EvnSection_id
				";

				$this->db->query($query, array(
					'EvnSection_CoeffCTP' => $kslp['EvnSection_CoeffCTP'],
					'EvnSection_TreatmentDiff' => $kslp['EvnSection_TreatmentDiff'],
					'EvnSection_id' => $es['EvnSection_id']
				));

				// и список КСЛП тоже для каждого движения группы refs #136745
				foreach($kslp['List'] as $one_kslp) {
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
						'EvnSection_id' => $es['EvnSection_id'],
						'EvnSectionKSLPLink_Code' => $one_kslp['Code'],
						'EvnSectionKSLPLink_Value' => $one_kslp['Value'],
						'pmUser_id' => $this->promedUserId
					));
				}
			}
		}
	}

	/**
	 * Группировка
	 */
	function getEvnSectionGroup($data = []) {
		$filter = "";
		$union = "";

		if (empty($data['EvnSection_pid'])) {
			// группировка при пересчёте КСГ в движениях
			$queryParams = [
				'EvnSection_pid' => $this->pid
			];
		} else {
			// группировка для формы
			$queryParams = [
				'EvnSection_pid' => $data['EvnSection_pid']
			];

			if (!empty($data['EvnSection_id'])) {
				$queryParams['EvnSection_id'] = $data['EvnSection_id'];
				$filter .= " and es.EvnSection_id <> :EvnSection_id";
			} else {
				$queryParams['EvnSection_id'] = null;
			}

			$union = "
				union all
				
				select
					:EvnSection_id as EvnSection_id,
					:Diag_id as Diag_id,
					cr.CureResult_Code,
					lsp.LpuSectionProfile_Code,
					ISNULL(:EvnSection_disDate, :EvnSection_setDate) as date,
					null as MesOld_Num,
					d.Diag_Code
				from
					v_Diag d (nolock)
					inner join v_PayType pt (nolock) on pt.PayType_id = :PayType_id
					left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = :LpuSectionProfile_id
					left join v_CureResult cr (nolock) on cr.CureResult_id = :CureResult_id
				where
					d.Diag_id = :Diag_id
					and :HTMedicalCareClass_id is null
					and pt.PayType_SysNick = 'oms'
			";

			$queryParams['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
			$queryParams['Diag_id'] = $data['Diag_id'];
			$queryParams['CureResult_id'] = $data['CureResult_id'];
			$queryParams['EvnSection_setDate'] = $data['EvnSection_setDate'];
			$queryParams['EvnSection_disDate'] = $data['EvnSection_disDate'];
			$queryParams['HTMedicalCareClass_id'] = $data['HTMedicalCareClass_id'];
			$queryParams['PayType_id'] = $data['PayType_id'];
		}

		$resp_es = $this->queryResult("
			select
				es.EvnSection_id,
				es.Diag_id,
				cr.CureResult_Code,
				lsp.LpuSectionProfile_Code,
				ISNULL(EvnSection_disDate, EvnSection_setDate) as date,
				mo.MesOld_Num,
				d.Diag_Code
			from
				v_EvnSection es (nolock)
				inner join v_PayType pt (nolock) on pt.PayType_id = es.PayType_id
				left join v_CureResult cr (nolock) on cr.CureResult_id = es.CureResult_id
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = es.LpuSectionProfile_id
				left join v_MesTariff mt (nolock) on mt.MesTariff_id = es.MesTariff_id
				left join v_MesOld mo (nolock) on mo.Mes_id = mt.Mes_id
				left join v_Diag d (nolock) on d.Diag_id = es.Diag_id
			where
				es.EvnSection_pid = :EvnSection_pid
				and es.HTMedicalCareClass_id is null
				and pt.PayType_SysNick = 'oms'
				{$filter}
				
				{$union}
				 
			order by
				date
		", $queryParams);

		// группировка №1.
		$groupped = [];
		$groupNum = 1;
		foreach ( $resp_es as $key => $respone ) {
			if (!isset($groupped[$groupNum])) {
				$groupped[$groupNum]['EvnSections'] = [];
			}

			$groupped[$groupNum]['EvnSections'][] = $respone;

			if (in_array($respone['CureResult_Code'], [1, 2])) {
				$groupNum++;
			}
		}

		// группировка №2.
		$groupped2 = [];
		foreach($groupped as $group) {
			if (count($group['EvnSections']) == 1) {
				// если группа, полученная на данном этапе, содержит только одно движение, то группировка окончена
				$groupped2[] = $group;
			} else {
				$ess = $group['EvnSections'];
				// группируем на подгруппы
				foreach($ess as $key => $value) {
					$ess[$key]['EvnSections'] = [$value];
				}
				// группируем родоразрешение по МКБ/профилю
				$esPregnKey = null; // движение по родам
				foreach($ess as $key => $value) {
					if ($value['LpuSectionProfile_Code'] == 5) {
						continue; // эти тут не учитываются
					}

					if (!isset($esPregnKey) && in_array($value['MesOld_Num'], ['st02.003', 'st02.004'])) {
						$esPregnKey = $key;
						continue;
					}

					if (!isset($esPregnKey)
						&& !in_array($value['MesOld_Num'], ['st02.003', 'st02.004'])
						&& mb_substr($value['Diag_Code'], 0, 3) >= 'O80' && mb_substr($value['Diag_Code'], 0, 3) <= 'O84'
					) {
						$esPregnKey = $key;
						continue;
					}

					// Если подряд идут движения, удовлетворяющие условиям:
						// Первое движение удовлетворяет хотя бы одному из условий:
							// •	КСГ движения = «4 Родоразрешение» st02.003;
							// •	КСГ движения = «5 Кесарево сечение» st02.004
						//или
							// •	КСГ движения <>«st02.003 Родоразрешение»;
							// •	КСГ движения <>«st02.004 Кесарево сечение»;
							// •	Основной диагноз движения входит в диапазон O80.0 – O84.9
						// Второе и последующие движения удовлетворяет одновременно условиям:
							// •	Основной диагноз = основному диагнозу первого движения;
							// •	Профиль = профилю первого движения;
							// •	В движении отсутствует услуга, являющаяся классификационным критерием КСГ из первого движения
					if (
						isset($esPregnKey)
						&& $value['Diag_id'] == $ess[$esPregnKey]['Diag_id']
						&& $value['LpuSectionProfile_Code'] == $ess[$esPregnKey]['LpuSectionProfile_Code']
						&& !empty($value['EvnSection_id'])
					) {
						// проверям наличие услуги во втором дижении для КСГ из первого движения
						$resp_eu = $this->queryResult("
							select top 1
								eu.EvnUsluga_id
							from
								v_EvnUsluga eu (nolock)
								inner join v_MesOldUslugaComplex mouc (nolock) on mouc.UslugaComplex_id = eu.UslugaComplex_id
								inner join v_MesOld mo (nolock) on mo.Mes_id = mouc.Mes_id
							where
								eu.EvnUsluga_pid = :EvnSection_id
								and mo.MesOld_Num = :MesOld_Num 
						", [
							'EvnSection_id' => $value['EvnSection_id'],
							'MesOld_Num' => $ess[$esPregnKey]['MesOld_Num']
						]);

						if (empty($resp_eu[0]['EvnUsluga_id'])) {
							$ess[$esPregnKey]['EvnSections'] = array_merge($ess[$esPregnKey]['EvnSections'], $value['EvnSections']);
							unset($ess[$key]); // ансетим  текущее движение
						}
					} else if (in_array($value['MesOld_Num'], ['st02.003', 'st02.004'])) {
						$esPregnKey = $key;
					} else if (!in_array($value['MesOld_Num'], ['st02.003', 'st02.004']) && mb_substr($value['Diag_Code'], 0, 3) >= 'O80' && mb_substr($value['Diag_Code'], 0, 3) <= 'O84') {
						$esPregnKey = $key;
					} else {
						$esPregnKey = null;
					}
				}

				// группируем реанимацию
				$hasGroups = true;
				while ($hasGroups) {
					$hasGroups = false;
					foreach ($ess as $key => $value) {
						// Если движение по реанимации, то оно группируется с предыдущим или со следующим движением.
						if ($value['LpuSectionProfile_Code'] == '5') {
							if (!empty($ess[$key - 1])) {
								// группируем движение с предыдущим
								$ess[$key - 1]['EvnSections'] = array_merge($ess[$key - 1]['EvnSections'], $value['EvnSections']);
								unset($ess[$key]); // ансетим движение по реанимации
								$ess = array_values($ess); // перенумеровываем
								$hasGroups = true;
								break; // пойдем в следующую итерацию
							} else if (!empty($ess[$key + 1])) {
								// группируем движение со следующим
								$ess[$key + 1]['EvnSections'] = array_merge($ess[$key + 1]['EvnSections'], $value['EvnSections']);
								unset($ess[$key]); // ансетим движение по реанимации
								$resp_es = array_values($resp_es); // перенумеровываем
								$hasGroups = true;
								break; // пойдем в следующую итерацию
							}
						}
					}
				}

				foreach($ess as $key => $value) {
					$groupped2[] = $value;
				}
			}
		}

		foreach($groupped2 as &$group) {
			$group['EvnSectionIds'] = [];
			$group['EvnSectionIdsWithoutReanim'] = [];
			foreach($group['EvnSections'] as $es) {
				$group['EvnSectionIds'][] = $es['EvnSection_id'];
				if ($es['LpuSectionProfile_Code'] != '5') {
					$group['EvnSectionIdsWithoutReanim'][] = $es['EvnSection_id'];
				}
			}
		}
		unset($group);

		return [
			'group1' => $groupped,
			'group2' => $groupped2
		];
	}

	/**
	 * Перегруппировка движений для всей КВС
	 * @task https://redmine.swan.perm.ru/issues/90346
	 */
	protected function _recalcIndexNum()
	{
		// убираем признаки со всех движений КВС
		$query = "
			update
				es with (rowlock)
			set
				es.EvnSection_IndexNum = null,
				es.EvnSection_IndexNum2 = null
			from
				EvnSection es
				inner join Evn e (nolock) on e.Evn_id = es.EvnSection_id
			where
				e.Evn_pid = :EvnSection_pid
		";
		$this->db->query($query, [
			'EvnSection_pid' => $this->pid
		]);

		$groupped = $this->getEvnSectionGroup();

		$groupNum = 0;
		foreach($groupped['group1'] as $group) {
			$groupNum++;
			$EvnSectionIds = [];
			foreach($group['EvnSections'] as $es) {
				$EvnSectionIds[] = $es['EvnSection_id'];
			}
			if (!empty($EvnSectionIds)) {
				$query = "
					update
						EvnSection with (rowlock)
					set
						EvnSection_IndexNum = :EvnSection_IndexNum
					where
						EvnSection_id in (" . implode(',', $EvnSectionIds) . ")
				";
				$this->db->query($query, [
					'EvnSection_IndexNum' => $groupNum
				]);
			}
		}



		$groupNum = 0;
		foreach($groupped['group2'] as $group) {
			$groupNum++;
			if (!empty($group['EvnSectionIds'])) {
				$query = "
					update
						EvnSection with (rowlock)
					set
						EvnSection_IndexNum2 = :EvnSection_IndexNum2
					where
						EvnSection_id in (" . implode(',', $group['EvnSectionIds']) . ")
				";
				$this->db->query($query, [
					'EvnSection_IndexNum2' => $groupNum
				]);
			}
		}

		if (!$this->_isRecalcScript) {
			// пересчёт КСГ
			$this->load->model('EvnSection_model', 'es_model');
			foreach($groupped['group2'] as $group) {
				foreach ($group['EvnSectionIds'] as $EvnSection_id) {
					$EvnSectionIds = [$EvnSection_id];
					if (!empty($group['EvnSectionIdsWithoutReanim'])) {
						$EvnSectionIds = $group['EvnSectionIdsWithoutReanim'];
					}
					$this->es_model->reset();
					$this->es_model->recalcKSGKPGKOEF($EvnSection_id, $this->sessionParams, [
						'EvnSectionIds' => $EvnSectionIds,
						'ignoreRecalcIndexNum' => true,
						'noNeedResetMesTariff' => true
					]);
				}
			}
		}
	}
}
