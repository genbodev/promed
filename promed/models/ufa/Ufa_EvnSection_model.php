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
* @version			ufa
*/

require_once(APPPATH.'models/EvnSection_model.php');

class Ufa_EvnSection_model extends EvnSection_model {
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

		if (empty($data['LpuSectionProfile_Code'])) {
			$data['LpuSectionProfile_Code'] = $this->getFirstResultFromQuery("select LpuSectionProfile_Code from v_LpuSectionProfile (nolock) where LpuSectionProfile_id = :LpuSectionProfile_id", $data);
		}
		if (empty($data['LpuSectionProfile_Code'])) {
			$data['LpuSectionProfile_Code'] = '';
		}

		if (substr($data['EvnSection_disDate'], 0, 4) >= '2019') {
			// алгоритм с 2019 года
			return $this->loadKSGKPGKOEF2019($data);
		} else if (substr($data['EvnSection_disDate'], 0, 4) >= '2018') {
			// алгоритм с 2018 года
			return $this->loadKSGKPGKOEF2018($data);
		} else if (substr($data['EvnSection_disDate'], 0, 4) >= '2017') {
			// алгоритм с 2017 года
			return $this->loadKSGKPGKOEF2017($data);
		} else if (substr($data['EvnSection_disDate'], 0, 4) >= '2016') {
			// алгоритм с 2016 года
			return $this->loadKSGKPGKOEF2016($data);
		} else if (substr($data['EvnSection_disDate'], 0, 4) >= '2015') {
			// алгоритм с 2015 года
			return $this->loadKSGKPGKOEF2015($data);
		}

		// проверяем группировку по реанимации, если последнее сгруппировавшееся движения в 2015 году, тогда используем и для этого алгоритм 2015 года
		$is2015 = false;
		$isReanim = false;
		/**
		 * 1. получаем отделение с текущего движения.
		 */
		$CurrentLpuSection_id = $data['LpuSection_id'];
		$ReanimLpuSection_id = $this->getFirstResultFromQuery("
			SELECT top 1
				case
					when ESNBLSP.LpuSection_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then ESNBLSP.LpuSection_id else null
				end as LpuSection_id
			FROM
				v_EvnSection es with (nolock)
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = :LpuSectionProfile_id
				outer apply(
					select top 1
						esnb.LpuSection_id
					from
						v_EvnSectionNarrowBed esnb (nolock)
					where
						esnb.EvnSectionNarrowBed_pid = es.EvnSection_id
					order by
						esnb.EvnSectionNarrowBed_setDate desc
				) ESNBLSP
			WHERE
				es.EvnSection_id = :EvnSection_id
		", array(
			'EvnSection_id' => $data['EvnSection_id'],
			'LpuSectionProfile_id' => $data['LpuSectionProfile_id']
		));

		if (!empty($ReanimLpuSection_id)) {
			$CurrentLpuSection_id = $ReanimLpuSection_id;
			$isReanim = true;
		}

		/**
		 * 2. получаем все остальные движения данной КВС.
		 */
		$query = "
			SELECT
				es.EvnSection_id,
				convert(varchar(10), es.EvnSection_setDate, 104) as EvnSection_setDate,
				convert(varchar(10), ISNULL(es.EvnSection_disDate, es.EvnSection_setDate), 104) as EvnSection_disDate,
				datediff(DAY, EvnSection_setDate, EvnSection_disDate) as EvnSection_Duration,
				case
					when ESNBLSP.LpuSection_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then 1 else 0
				end as isReanim,
				case
					when ESNBLSP.LpuSection_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then ESNBLSP.LpuSection_id else es.LpuSection_id
				end as LpuSection_id
			FROM
				v_EvnSection es with (nolock)
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = es.LpuSectionProfile_id
				outer apply(
					select top 1
						esnb.LpuSection_id
					from
						v_EvnSectionNarrowBed esnb (nolock)
					where
						esnb.EvnSectionNarrowBed_pid = es.EvnSection_id
					order by
						esnb.EvnSectionNarrowBed_setDate desc
				) ESNBLSP
			WHERE
				es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
			order by
				es.EvnSection_setDate
		";
		$result = $this->db->query($query, array(
			'EvnSection_id' => $data['EvnSection_id'],
			'EvnSection_pid' => $data['EvnSection_pid']
		));

		if (is_object($result)) {
			$resp = $result->result('array');
			/**
			 * 3. ищем движения которые нужно сгруппировать с текущим
			 */
			foreach($resp as $respone) {
				if ($respone['LpuSection_id'] == $CurrentLpuSection_id) {
					if ($respone['isReanim'] == 1) {
						$isReanim = true;
					}
					if (date('Y',strtotime($respone['EvnSection_disDate'])) == 2015) {
						$is2015 = true;
					}
				} else if (strtotime($data['EvnSection_setDate']) >= strtotime($respone['EvnSection_setDate'])) {
					// если другое отделение до нашего движения то не считаем его и всё что до него.
					$is2015 = false;
				} else {
					// если другое отделение после нашего движения то не считаем его и останавливаем цикл
					break;
				}
			}
		}

		if ($isReanim && $is2015) {
			// алгоритм с 2015 года
			return $this->loadKSGKPGKOEF2015($data);
		}

		// группировка по диагнозу
		$Diag_pid = $this->getFirstResultFromQuery("
			select top 1
				d2.Diag_pid
			from
				v_Diag d (nolock)
				inner join v_Diag d1 (nolock) on d1.Diag_id = d.Diag_pid -- группа диагнозов
				inner join v_Diag d2 (nolock) on d2.Diag_id = d1.Diag_pid -- ещё группа диагнозов
			where
				d.Diag_id = :Diag_id
		", array(
			'Diag_id' => $data['Diag_id']
		));

		if (!empty($Diag_pid) && empty($data['HTMedicalCareClass_id']) && !in_array($data['LpuSectionProfile_Code'], array('1040', '2040', '3040', '1087', '2087', '3087', '1039', '2039', '3039', '1074', '2074', '3074', '1084', '2084', '3084', '1036', '2036'))) {
			// достаём движения с той же категорией диагнозов, что и в текущем движении
			$query = "
				SELECT top 1
					es.EvnSection_id,
					convert(varchar(10), ISNULL(es.EvnSection_disDate, es.EvnSection_setDate), 120) as EvnSection_disDate
				FROM
					v_EvnSection es with (nolock)
					inner join v_Diag d (nolock) on d.Diag_id = es.Diag_id
					inner join v_Diag d1 (nolock) on d1.Diag_id = d.Diag_pid -- группа диагнозов
					inner join v_Diag d2 (nolock) on d2.Diag_id = d1.Diag_pid -- ещё группа диагнозов
					left join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
					left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
				WHERE
					es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
					and d2.Diag_pid = :Diag_pid
					and YEAR(ISNULL(es.EvnSection_disDate, es.EvnSection_setDate)) = 2015
					and es.HTMedicalCareClass_id is null
					and (isnull(lsp.LpuSectionProfile_Code,'') not in ('1040', '2040', '3040', '1087', '2087', '3087', '1039', '2039', '3039', '1074', '2074', '3074', '1084', '2084', '3084', '1036', '2036'))
				order by
					es.EvnSection_setDate desc
			";

			$result = $this->db->query($query, array(
				'EvnSection_id' => $data['EvnSection_id'],
				'EvnSection_pid' => $data['EvnSection_pid'],
				'Diag_pid' => $Diag_pid
			));

			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['EvnSection_id'])) {
					$data['DiagEvnSection_disDate'] = $resp[0]['EvnSection_disDate'];
					return $this->loadKSGKPGKOEF2015($data);
				}
			}
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
								mo.Mes_Code as KSG,
								mo.Mes_id,
								mt.MesTariff_Value as KOEF,
								mt.MesTariff_id,
								mokpg.KPG,
								mokpg.Mes_kid
							from
								v_MesOld mo (nolock)
								left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
								outer apply(
									select top 1
										mo2.Mes_id as Mes_kid,
										mo2.Mes_Code as KPG
									from
										v_MesOld mo2 (nolock)
										inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
									where
										ml.Mes_id = mo.Mes_id
										and mo2.Mes_begDT <= :EvnSection_disDate
										and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								) mokpg
							where
								mo.Mes_Code = '127' and mo.MesType_id = 3 -- КСГ терапевтическая
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

		// 1 Ищем терапевтическую КСГ
		// 1.1.	Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
		if (!empty($data['EvnSection_id'])) {
			$query = "
				select top 1
					mo.Mes_Code as KSG,
					mo.Mes_id,
					mt.MesTariff_Value as KOEF,
					mt.MesTariff_id,
					mokpg.KPG,
					mokpg.Mes_kid
				from v_EvnUsluga eu (nolock)
					inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu (nolock) on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id and mo.MesType_id = 3 -- КСГ терапевтическая
					outer apply(
						select top 1
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code as KPG
						from
							v_MesOld mo2 (nolock)
							inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					) mokpg
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
						or (:Person_AgeDays >= 91 and :Person_AgeDays <= 365 and mu.MesAgeGroup_id = 10)
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
					$KSGTerr = $resp[0];
				}
			}
		}
		// 1.2.	Пробуем определить КСГ по наличию диагноза, иначе
		if (!empty($data['Diag_id'])) {
			$query = "
				select top 1
					d.Diag_Code,
					mo.Mes_Code as KSG,
					mo.Mes_id,
					mt.MesTariff_Value as KOEF,
					mt.MesTariff_id,
					mokpg.KPG,
					mokpg.Mes_kid
				from v_MesOldDiag mod (nolock)
					inner join v_MesOld mo (nolock) on mo.Mes_id = mod.Mes_id and mo.MesType_id = 3 -- КСГ терапевтическая
					left join v_Diag d (nolock) on d.Diag_id = mod.Diag_id
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					outer apply(
						select top 1
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code as KPG
						from
							v_MesOld mo2 (nolock)
							inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					) mokpg
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
					and (IsNull(mod.MesOldDiag_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
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
					if (empty($KSGTerr['KOEF']) || $resp[0]['KOEF'] > $KSGTerr['KOEF']) {
						$KSGTerr = $resp[0];
					}

					if ( in_array($resp[0]['Diag_Code'], array('I20.1', 'I20.8', 'I20.9', 'I24.0', 'I24.1', 'I24.8', 'I24.9', 'I25.0', 'I25.1', 'I25.2', 'I25.3', 'I25.4', 'I25.5', 'I25.6', 'I25.8', 'I25.9')) ) {
						// проверяем наличие A06.10.006
						$EvnUsluga_id = $this->getFirstResultFromQuery("select top 1 EvnUsluga_id from v_EvnUsluga eu (nolock) inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id where uc.UslugaComplex_Code in ('A06.10.006') and eu.EvnUsluga_pid = :EvnSection_id and eu.PayType_id = :PayTypeOms_id", array('EvnSection_id' => $data['EvnSection_id'], 'PayTypeOms_id' => $data['PayTypeOms_id']));
						// если нашли, то берем ксг по ней 107 (хирургическая)
						if (!empty($EvnUsluga_id)) {
							$query = "
								select top 1
									mo.Mes_Code as KSG,
									mo.Mes_id,
									mt.MesTariff_Value as KOEF,
									mt.MesTariff_id,
									mokpg.KPG,
									mokpg.Mes_kid
								from
									v_MesOld mo (nolock)
									left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
									outer apply(
										select top 1
											mo2.Mes_id as Mes_kid,
											mo2.Mes_Code as KPG
										from
											v_MesOld mo2 (nolock)
											inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
										where
											ml.Mes_id = mo.Mes_id
											and mo2.Mes_begDT <= :EvnSection_disDate
											and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
									) mokpg
								where
									mo.Mes_Code = '107' and mo.MesType_id = 2 -- КСГ хирургическая
									and mo.Mes_begDT <= :EvnSection_disDate
									and (IsNull(mo.Mes_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
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
									if (empty($KSGOper['KOEF']) || $resp_ksg[0]['KOEF'] > $KSGOper['KOEF']) {
										$KSGOper = $resp_ksg[0];
										$KSGTerr = false;
									}
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
									mo.Mes_Code as KSG,
									mo.Mes_id,
									mt.MesTariff_Value as KOEF,
									mt.MesTariff_id,
									mokpg.KPG,
									mokpg.Mes_kid
								from
									v_MesOld mo (nolock)
									left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
									outer apply(
										select top 1
											mo2.Mes_id as Mes_kid,
											mo2.Mes_Code as KPG
										from
											v_MesOld mo2 (nolock)
											inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
										where
											ml.Mes_id = mo.Mes_id
											and mo2.Mes_begDT <= :EvnSection_disDate
											and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
									) mokpg
								where
									mo.Mes_Code = '109' and mo.MesType_id = 2 -- КСГ хирургическая
									and mo.Mes_begDT <= :EvnSection_disDate
									and (IsNull(mo.Mes_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
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
									if (empty($KSGOper['KOEF']) || $resp_ksg[0]['KOEF'] > $KSGOper['KOEF']) {
										$KSGOper = $resp_ksg[0];
										$KSGTerr = false;
									}
								}
							}
						}
					}
				}
			}
		}

		// 2 Ищем хирургическую КСГ
		// 2.1.	Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
		if (!empty($data['EvnSection_id'])) {
			$query = "
				select top 1
					mo.Mes_Code as KSG,
					mo.Mes_id,
					mt.MesTariff_Value as KOEF,
					mt.MesTariff_id,
					mokpg.KPG,
					mokpg.Mes_kid
				from v_EvnUsluga eu (nolock)
					inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu (nolock) on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id and mo.MesType_id = 2 -- КСГ хирургическая
					outer apply(
						select top 1
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code as KPG
						from
							v_MesOld mo2 (nolock)
							inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					) mokpg
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
						or (:Person_AgeDays >= 91 and :Person_AgeDays <= 365 and mu.MesAgeGroup_id = 10)
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
					if (empty($KSGOper['KOEF']) || $resp[0]['KOEF'] > $KSGOper['KOEF']) {
						$KSGOper = $resp[0];
					}
				}
			}
		}
		// 2.2.	Пробуем определить КСГ по наличию диагноза, иначе
		if (!empty($data['Diag_id'])) {
			$query = "
				select top 1
					d.Diag_Code,
					mo.Mes_Code as KSG,
					mo.Mes_id,
					mt.MesTariff_Value as KOEF,
					mt.MesTariff_id,
					mokpg.KPG,
					mokpg.Mes_kid
				from v_MesOldDiag mod (nolock)
					inner join v_MesOld mo (nolock) on mo.Mes_id = mod.Mes_id and mo.MesType_id = 2 -- КСГ хирургическая
					left join v_Diag d (nolock) on d.Diag_id = mod.Diag_id
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					outer apply(
						select top 1
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code as KPG
						from
							v_MesOld mo2 (nolock)
							inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					) mokpg
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
					and (IsNull(mod.MesOldDiag_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
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
					if (empty($KSGOper['KOEF']) || $resp[0]['KOEF'] > $KSGOper['KOEF']) {
						$KSGOper = $resp[0];
					}

					if ( in_array($resp[0]['Diag_Code'], array('I20.1', 'I20.8', 'I20.9', 'I24.0', 'I24.1', 'I24.8', 'I24.9', 'I25.0', 'I25.1', 'I25.2', 'I25.3', 'I25.4', 'I25.5', 'I25.6', 'I25.8', 'I25.9')) ) {
						// проверяем наличие A06.10.006
						$EvnUsluga_id = $this->getFirstResultFromQuery("select top 1 EvnUsluga_id from v_EvnUsluga eu (nolock) inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id where uc.UslugaComplex_Code in ('A06.10.006') and eu.EvnUsluga_pid = :EvnSection_id and eu.PayType_id = :PayTypeOms_id", array('EvnSection_id' => $data['EvnSection_id'], 'PayTypeOms_id' => $data['PayTypeOms_id']));
						// если нашли, то берем ксг по ней 107 (хирургическая)
						if (!empty($EvnUsluga_id)) {
							$query = "
								select top 1
									mo.Mes_Code as KSG,
									mo.Mes_id,
									mt.MesTariff_Value as KOEF,
									mt.MesTariff_id,
									mokpg.KPG,
									mokpg.Mes_kid
								from
									v_MesOld mo (nolock)
									left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
									outer apply(
										select top 1
											mo2.Mes_id as Mes_kid,
											mo2.Mes_Code as KPG
										from
											v_MesOld mo2 (nolock)
											inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
										where
											ml.Mes_id = mo.Mes_id
											and mo2.Mes_begDT <= :EvnSection_disDate
											and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
									) mokpg
								where
									mo.Mes_Code = '107' and mo.MesType_id = 2 -- КСГ хирургическая
									and mo.Mes_begDT <= :EvnSection_disDate
									and (IsNull(mo.Mes_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
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
									if (empty($KSGOper['KOEF']) || $resp_ksg[0]['KOEF'] > $KSGOper['KOEF']) {
										$KSGOper = $resp_ksg[0];
										$KSGTerr = false;
									}
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
									mo.Mes_Code as KSG,
									mo.Mes_id,
									mt.MesTariff_Value as KOEF,
									mt.MesTariff_id,
									mokpg.KPG,
									mokpg.Mes_kid
								from
									v_MesOld mo (nolock)
									left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
									outer apply(
										select top 1
											mo2.Mes_id as Mes_kid,
											mo2.Mes_Code as KPG
										from
											v_MesOld mo2 (nolock)
											inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
										where
											ml.Mes_id = mo.Mes_id
											and mo2.Mes_begDT <= :EvnSection_disDate
											and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
									) mokpg
								where
									mo.Mes_Code = '109' and mo.MesType_id = 2 -- КСГ хирургическая
									and mo.Mes_begDT <= :EvnSection_disDate
									and (IsNull(mo.Mes_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
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
									if (empty($KSGOper['KOEF']) || $resp_ksg[0]['KOEF'] > $KSGOper['KOEF']) {
										$KSGOper = $resp_ksg[0];
										$KSGTerr = false;
									}
								}
							}
						}
					}
				}
			}
		}

		// Если для движения не определилось КСГ, то КПГ берём из профиля отделения
		if (!$KSGOper && !$KSGTerr && !$KSGFromPolyTrauma) {
			// 3.1	Определяем профиль отеделния, если '1035','2035','3035' и есть профильные койки до берем профиль с последней койки
			if (!empty($data['EvnSection_id'])) {
				if (empty($data['LpuSectionProfile_id'])) {
					$data['LpuSectionProfile_id'] = null;
				}
				$data['LpuSectionProfile_id'] = $this->getFirstResultFromQuery("
					SELECT top 1
						case
							when ESNBLSP.LpuSectionProfile_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then ESNBLSP.LpuSectionProfile_id
							else lsp.LpuSectionProfile_id end as LpuSectionProfile_id
					FROM
						v_EvnSection es with (nolock)
						left join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
						left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ISNULL(:LpuSectionProfile_id, ls.LpuSectionProfile_id)
						outer apply(
							select top 1
								esnbls.LpuSectionProfile_id
							from
								v_EvnSectionNarrowBed esnb (nolock)
								inner join v_LpuSection esnbls (nolock) on esnbls.LpuSection_id = esnb.LpuSection_id
							where
								esnb.EvnSectionNarrowBed_pid = es.EvnSection_id
							order by
								esnb.EvnSectionNarrowBed_setDate desc
						) ESNBLSP
					WHERE
						es.EvnSection_id = :EvnSection_id
				", array('EvnSection_id' => $data['EvnSection_id'], 'LpuSectionProfile_id' => $data['LpuSectionProfile_id']));
			}

			// 3.2	Пробуем определить КПГ по профилю отделения
			if (!empty($data['LpuSectionProfile_id'])) {
				$query = "
					select top 1
						mokpg.Mes_Code as KPG,
						mokpg.Mes_id,
						mtkpg.MesTariff_Value as KOEF,
						mtkpg.MesTariff_id
					from MesOld mokpg (nolock)
						left join v_MesTariff mtkpg (nolock) on mtkpg.Mes_id = mokpg.Mes_id -- Коэффициент КПГ
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
						$KPGFromLpuSectionProfile = $resp[0];
					}
				}
			}
		}

		// дополнительная логика (refs #35319)
		// Если по услуге A16.20.079 определилась 12-ая КСГ, то надо проверить терапевтическую КСГ. Если терапевтическая КСГ равна 11-ой, то и в хирургическую КСГ записываем 11. Если терапевтическая КСГ не равна 11-ой, то в хирургической КСГ оставляем 12.
		if ($KSGOper && $KSGTerr) {
			if ($KSGOper['KSG'] == '12' && $KSGTerr['KSG'] == '11') {
				// проверяем наличие A16.20.079
				$EvnUsluga_id = $this->getFirstResultFromQuery("select top 1 EvnUsluga_id from v_EvnUsluga eu (nolock) inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id where uc.UslugaComplex_Code in ('A16.20.079') and eu.EvnUsluga_pid = :EvnSection_id and eu.PayType_id = :PayTypeOms_id", array('EvnSection_id' => $data['EvnSection_id'], 'PayTypeOms_id' => $data['PayTypeOms_id']));
				// если нашли, то берем ксг из диагноза
				if (!empty($EvnUsluga_id)) {
					$KSGOper = false;
				}
			}
		}

		$response = array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'success' => true);

		if ($KPGFromLpuSectionProfile) {
			$response['KPG'] = $KPGFromLpuSectionProfile['KPG'];
			$response['Mes_kid'] = $KPGFromLpuSectionProfile['Mes_id'];
		}

		if ($KSGOper && $KSGTerr) {
			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			if ($KSGOper['KOEF'] > $KSGTerr['KOEF']) {
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

		$isReanim = false;
		$ReanimSetDate = $data['EvnSection_setDate'];
		$ReanimDisDate = $data['EvnSection_disDate'];

		/**
		 * 1. получаем отделение с текущего движения.
		 */
		$CurrentLpuSection_id = $data['LpuSection_id'];
		$ReanimLpuSection_id = $this->getFirstResultFromQuery("
			SELECT top 1
				case
					when ESNBLSP.LpuSection_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then ESNBLSP.LpuSection_id else null
				end as LpuSection_id
			FROM
				v_EvnSection es with (nolock)
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = :LpuSectionProfile_id
				outer apply(
					select top 1
						esnb.LpuSection_id
					from
						v_EvnSectionNarrowBed esnb (nolock)
					where
						esnb.EvnSectionNarrowBed_pid = es.EvnSection_id
					order by
						esnb.EvnSectionNarrowBed_setDate desc
				) ESNBLSP
			WHERE
				es.EvnSection_id = :EvnSection_id
		", array(
			'EvnSection_id' => $data['EvnSection_id'],
			'LpuSectionProfile_id' => $data['LpuSectionProfile_id']
		));

		if (!empty($ReanimLpuSection_id)) {
			$CurrentLpuSection_id = $ReanimLpuSection_id;
			$isReanim = true;
		}

		/**
		 * 2. получаем все остальные движения данной КВС.
		 */
		$query = "
			SELECT
				es.EvnSection_id,
				convert(varchar(10), es.EvnSection_setDate, 120) as EvnSection_setDate,
				convert(varchar(10), es.EvnSection_disDate, 120) as EvnSection_disDate,
				datediff(DAY, EvnSection_setDate, EvnSection_disDate) as EvnSection_Duration,
				case
					when ESNBLSP.LpuSection_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then 1 else 0
				end as isReanim,
				case
					when ESNBLSP.LpuSection_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then ESNBLSP.LpuSection_id else es.LpuSection_id
				end as LpuSection_id
			FROM
				v_EvnSection es with (nolock)
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = es.LpuSectionProfile_id
				outer apply(
					select top 1
						esnb.LpuSection_id
					from
						v_EvnSectionNarrowBed esnb (nolock)
					where
						esnb.EvnSectionNarrowBed_pid = es.EvnSection_id
					order by
						esnb.EvnSectionNarrowBed_setDate desc
				) ESNBLSP
			WHERE
				es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
			order by
				es.EvnSection_setDate
		";
		$result = $this->db->query($query, array(
			'EvnSection_id' => $data['EvnSection_id'],
			'EvnSection_pid' => $data['EvnSection_pid']
		));

		if (is_object($result)) {
			$resp = $result->result('array');
			/**
			 * 3. ищем движения которые нужно сгруппировать с текущим
			 */
			foreach($resp as $respone) {
				if ($respone['LpuSection_id'] == $CurrentLpuSection_id) {
					if ($respone['isReanim'] == 1) {
						$isReanim = true;
					}
					if (empty($ReanimSetDate) || strtotime($respone['EvnSection_setDate']) < strtotime($ReanimSetDate)) {
						$ReanimSetDate = $respone['EvnSection_setDate'];
					}
					if (empty($ReanimDisDate) || (strtotime($ReanimDisDate) < strtotime($respone['EvnSection_setDate']))) {
						$ReanimDisDate = $respone['EvnSection_setDate'];
					}
					if (!empty($respone['EvnSection_disDate']) && (empty($ReanimDisDate) || (strtotime($ReanimDisDate) < strtotime($respone['EvnSection_disDate'])))) {
						$ReanimDisDate = $respone['EvnSection_disDate'];
					}
				} else if (strtotime($data['EvnSection_setDate']) >= strtotime($respone['EvnSection_setDate'])) {
					// если другое отделение до нашего движения то не считаем его и всё что до него.
					$ReanimSetDate = $respone['EvnSection_setDate'];
				} else {
					// если другое отделение после нашего движения то не считаем его и останавливаем цикл
					break;
				}
			}
		}

		if ($isReanim) {
			$data['EvnSection_setDate'] = $ReanimSetDate;
			$data['EvnSection_disDate'] = $ReanimDisDate;
		}

		if (substr($data['EvnSection_disDate'], 0, 4) >= '2016') {
			// алгоритм с 2016 года
			return $this->loadKSGKPGKOEF2016($data);
		}

		// группировка по диагнозу
		$Diag_pid = $this->getFirstResultFromQuery("
			select top 1
				d2.Diag_pid
			from
				v_Diag d (nolock)
				inner join v_Diag d1 (nolock) on d1.Diag_id = d.Diag_pid -- группа диагнозов
				inner join v_Diag d2 (nolock) on d2.Diag_id = d1.Diag_pid -- ещё группа диагнозов
			where
				d.Diag_id = :Diag_id
		", array(
			'Diag_id' => $data['Diag_id']
		));

		if (!empty($Diag_pid) && empty($data['HTMedicalCareClass_id']) && !in_array($data['LpuSectionProfile_Code'], array('1040', '2040', '3040', '1087', '2087', '3087', '1039', '2039', '3039', '1074', '2074', '3074', '1084', '2084', '3084', '1036', '2036'))) {
			// достаём движения с той же категорией диагнозов, что и в текущем движении
			$query = "
				SELECT top 1
					es.EvnSection_id,
					convert(varchar(10), ISNULL(es.EvnSection_disDate, es.EvnSection_setDate), 120) as EvnSection_disDate
				FROM
					v_EvnSection es with (nolock)
					inner join v_Diag d (nolock) on d.Diag_id = es.Diag_id
					inner join v_Diag d1 (nolock) on d1.Diag_id = d.Diag_pid -- группа диагнозов
					inner join v_Diag d2 (nolock) on d2.Diag_id = d1.Diag_pid -- ещё группа диагнозов
					left join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
					left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
				WHERE
					es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
					and d2.Diag_pid = :Diag_pid
					and YEAR(ISNULL(es.EvnSection_disDate, es.EvnSection_setDate)) = 2016
					and es.HTMedicalCareClass_id is null
					and (isnull(lsp.LpuSectionProfile_Code,'') not in ('1040', '2040', '3040', '1087', '2087', '3087', '1039', '2039', '3039', '1074', '2074', '3074', '1084', '2084', '3084', '1036', '2036'))
				order by
					es.EvnSection_setDate desc
			";

			$result = $this->db->query($query, array(
				'EvnSection_id' => $data['EvnSection_id'],
				'EvnSection_pid' => $data['EvnSection_pid'],
				'Diag_pid' => $Diag_pid
			));

			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['EvnSection_id'])) {
					$data['DiagEvnSection_disDate'] = $resp[0]['EvnSection_disDate'];
					return $this->loadKSGKPGKOEF2016($data);
				}
			}
		}

		// считаем длительность пребывания
		$datediff = strtotime($data['EvnSection_disDate']) - strtotime($data['EvnSection_setDate']);
		$data['Duration'] = floor($datediff/(60*60*24));
		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			$data['Duration'] += 1; // для дневного +1
		}

		// если была группировка по диагнозу из 2014 года, то связки берем на дату последнего движения.
		if (!empty($data['DiagEvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['DiagEvnSection_disDate'];
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
								mo.Mes_Code as KSG,
								mo.Mes_id,
								mt.MesTariff_Value as KOEF,
								mt.MesTariff_id,
								mokpg.KPG,
								mokpg.Mes_kid
							from
								v_MesOld mo (nolock)
								left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
								outer apply(
									select top 1
										mo2.Mes_id as Mes_kid,
										mo2.Mes_Code as KPG
									from
										v_MesOld mo2 (nolock)
										inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
									where
										ml.Mes_id = mo.Mes_id
										and mo2.Mes_begDT <= :EvnSection_disDate
										and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								) mokpg
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
					eu.EvnUsluga_id,
					mo.Mes_Code as KSG,
					mo.Mes_id,
					mt.MesTariff_Value as KOEF,
					mt.MesTariff_id,
					mokpg.KPG,
					mokpg.Mes_kid,
					mo.MesType_id
				from v_EvnUsluga eu (nolock)
					inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu (nolock) on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					outer apply(
						select top 1
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code as KPG
						from
							v_MesOld mo2 (nolock)
							inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					) mokpg
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
						or (:Person_AgeDays >= 91 and :Person_AgeDays <= 365 and mu.MesAgeGroup_id = 10)
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
					// ищем максимальную КСГ среди разных услуг.
					foreach ($KSGOperArray as $KSGOperOne) {
						if ($KSGOperOne['MesType_id'] == 3) {
							if (empty($CurUsluga) || $CurUsluga != $KSGOperOne['EvnUsluga_id']) {
								$CurUsluga = $KSGOperOne['EvnUsluga_id'];
								if (empty($KSGTerr) || $KSGOperOne['KOEF'] > $KSGTerr['KOEF']) {
									$KSGTerr = $KSGOperOne;
								}
							}
						} else {
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
				select top 1
					d.Diag_Code,
					mo.Mes_Code as KSG,
					mo.Mes_id,
					mt.MesTariff_Value as KOEF,
					mt.MesTariff_id,
					mokpg.KPG,
					mokpg.Mes_kid
				from v_MesOldUslugaComplex mu (nolock)
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					left join v_Diag d (nolock) on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					outer apply(
						select top 1
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code as KPG
						from
							v_MesOld mo2 (nolock)
							inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					) mokpg
				where
					(mu.Diag_id = :Diag_id OR (mo.Mes_Code in ('44','92') and mu.Diag_id IN (select Diag_id from v_EvnDiagPS (nolock) where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id)))
					and (mu.UslugaComplex_id is null OR mu.UslugaComplex_id IN (select eu.UslugaComplex_id from v_EvnUsluga eu (nolock) where eu.EvnUsluga_pid = :EvnSection_id and eu.EvnClass_id in (43,22,29) and eu.PayType_id = :PayTypeOms_id))
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
						or (:Person_AgeDays >= 91 and :Person_AgeDays <= 365 and mu.MesAgeGroup_id = 10)
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
					SELECT top 1
						case
							when ESNBLSP.LpuSectionProfile_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then ESNBLSP.LpuSectionProfile_id
							else lsp.LpuSectionProfile_id end as LpuSectionProfile_id
					FROM
						v_EvnSection es with (nolock)
						left join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
						left join v_LpuSectionProfile lsp (nolock) on ".$filterProfile."
						outer apply(
							select top 1
								esnbls.LpuSectionProfile_id
							from
								v_EvnSectionNarrowBed esnb (nolock)
								inner join v_LpuSection esnbls (nolock) on esnbls.LpuSection_id = esnb.LpuSection_id
							where
								esnb.EvnSectionNarrowBed_pid = es.EvnSection_id
							order by
								esnb.EvnSectionNarrowBed_setDate desc
						) ESNBLSP
					WHERE
						es.EvnSection_id = :EvnSection_id
				", array('EvnSection_id' => $data['EvnSection_id'], 'LpuSectionProfile_id' => $data['LpuSectionProfile_id']));
			}

			// 3.2	Пробуем определить КПГ по профилю отделения
			if (!empty($data['LpuSectionProfile_id'])) {
				$query = "
					select top 1
						mokpg.Mes_Code as KPG,
						mokpg.Mes_id,
						mtkpg.MesTariff_Value as KOEF,
						mtkpg.MesTariff_id
					from MesOld mokpg (nolock)
						left join v_MesTariff mtkpg (nolock) on mtkpg.Mes_id = mokpg.Mes_id -- Коэффициент КПГ
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
				if (in_array($KSGTerr['KSG'], array(97, 98))) {
					/**
					 * Если в дневном стационаре определились КСГ 98 и 99 или 98 и 100 (98 определяете по диагнозу и услуге, а 99 и 100 только по услуге), то:
					 * если длительность лечения менее 14 дней, то КСГ должно быть 99 или 100
					 * если длительность более или равна 14 дней, то КСГ определяется как 98
					 */
					if (in_array($data['LpuUnitType_id'], array('6','7','9')) && $data['Duration'] < 14) {
						foreach($KSGOperArray as $respone) {
							if (in_array($respone['KSG'], array(99,100))) {
								$KSGTerr = $respone;
								$response['Mes_tid'] = null;
								$response['Mes_sid'] = $KSGTerr['Mes_id'];
								break;
							}
						}
					}
				}
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

		$isReanim = false;
		$ReanimSetDate = $data['EvnSection_setDate'];
		$ReanimDisDate = $data['EvnSection_disDate'];

		/**
		 * 1. получаем отделение с текущего движения.
		 */
		$CurrentLpuSection_id = $data['LpuSection_id'];
		$ReanimLpuSection_id = $this->getFirstResultFromQuery("
			SELECT top 1
				case
					when ESNBLSP.LpuSection_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then ESNBLSP.LpuSection_id else null
				end as LpuSection_id
			FROM
				v_EvnSection es with (nolock)
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = :LpuSectionProfile_id
				outer apply(
					select top 1
						esnb.LpuSection_id
					from
						v_EvnSectionNarrowBed esnb (nolock)
					where
						esnb.EvnSectionNarrowBed_pid = es.EvnSection_id
					order by
						esnb.EvnSectionNarrowBed_setDate desc
				) ESNBLSP
			WHERE
				es.EvnSection_id = :EvnSection_id
		", array(
			'EvnSection_id' => $data['EvnSection_id'],
			'LpuSectionProfile_id' => $data['LpuSectionProfile_id']
		));

		if (!empty($ReanimLpuSection_id)) {
			$CurrentLpuSection_id = $ReanimLpuSection_id;
			$isReanim = true;
		}

		/**
		 * 2. получаем все остальные движения данной КВС.
		 */
		$query = "
			SELECT
				es.EvnSection_id,
				convert(varchar(10), es.EvnSection_setDate, 120) as EvnSection_setDate,
				convert(varchar(10), es.EvnSection_disDate, 120) as EvnSection_disDate,
				datediff(DAY, EvnSection_setDate, EvnSection_disDate) as EvnSection_Duration,
				case
					when ESNBLSP.LpuSection_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then 1 else 0
				end as isReanim,
				case
					when ESNBLSP.LpuSection_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then ESNBLSP.LpuSection_id else es.LpuSection_id
				end as LpuSection_id
			FROM
				v_EvnSection es with (nolock)
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = es.LpuSectionProfile_id
				outer apply(
					select top 1
						esnb.LpuSection_id
					from
						v_EvnSectionNarrowBed esnb (nolock)
					where
						esnb.EvnSectionNarrowBed_pid = es.EvnSection_id
					order by
						esnb.EvnSectionNarrowBed_setDate desc
				) ESNBLSP
			WHERE
				es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
			order by
				es.EvnSection_setDate
		";
		$result = $this->db->query($query, array(
			'EvnSection_id' => $data['EvnSection_id'],
			'EvnSection_pid' => $data['EvnSection_pid']
		));

		if (is_object($result)) {
			$resp = $result->result('array');
			/**
			 * 3. ищем движения которые нужно сгруппировать с текущим
			 */
			foreach($resp as $respone) {
				if ($respone['LpuSection_id'] == $CurrentLpuSection_id) {
					if ($respone['isReanim'] == 1) {
						$isReanim = true;
					}
					if (empty($ReanimSetDate) || strtotime($respone['EvnSection_setDate']) < strtotime($ReanimSetDate)) {
						$ReanimSetDate = $respone['EvnSection_setDate'];
					}
					if (empty($ReanimDisDate) || (strtotime($ReanimDisDate) < strtotime($respone['EvnSection_setDate']))) {
						$ReanimDisDate = $respone['EvnSection_setDate'];
					}
					if (!empty($respone['EvnSection_disDate']) && (empty($ReanimDisDate) || (strtotime($ReanimDisDate) < strtotime($respone['EvnSection_disDate'])))) {
						$ReanimDisDate = $respone['EvnSection_disDate'];
					}
				} else if (strtotime($data['EvnSection_setDate']) >= strtotime($respone['EvnSection_setDate'])) {
					// если другое отделение до нашего движения то не считаем его и всё что до него.
					$ReanimSetDate = $respone['EvnSection_setDate'];
				} else {
					// если другое отделение после нашего движения то не считаем его и останавливаем цикл
					break;
				}
			}
		}

		if ($isReanim) {
			$data['EvnSection_setDate'] = $ReanimSetDate;
			$data['EvnSection_disDate'] = $ReanimDisDate;
		}

		if (substr($data['EvnSection_disDate'], 0, 4) >= '2017') {
			// алгоритм с 2017 года
			return $this->loadKSGKPGKOEF2017($data);
		}

		// группировка по диагнозу
		$Diag_pid = $this->getFirstResultFromQuery("
			select top 1
				d2.Diag_pid
			from
				v_Diag d (nolock)
				inner join v_Diag d1 (nolock) on d1.Diag_id = d.Diag_pid -- группа диагнозов
				inner join v_Diag d2 (nolock) on d2.Diag_id = d1.Diag_pid -- ещё группа диагнозов
			where
				d.Diag_id = :Diag_id
		", array(
			'Diag_id' => $data['Diag_id']
		));

		if (!empty($Diag_pid) && empty($data['HTMedicalCareClass_id']) && !in_array($data['LpuSectionProfile_Code'], array('1040', '2040', '3040', '1087', '2087', '3087', '1039', '2039', '3039', '1074', '2074', '3074', '1084', '2084', '3084', '1036', '2036'))) {
			// достаём движения с той же категорией диагнозов, что и в текущем движении
			$query = "
				SELECT top 1
					es.EvnSection_id,
					convert(varchar(10), ISNULL(es.EvnSection_disDate, es.EvnSection_setDate), 120) as EvnSection_disDate
				FROM
					v_EvnSection es with (nolock)
					inner join v_Diag d (nolock) on d.Diag_id = es.Diag_id
					inner join v_Diag d1 (nolock) on d1.Diag_id = d.Diag_pid -- группа диагнозов
					inner join v_Diag d2 (nolock) on d2.Diag_id = d1.Diag_pid -- ещё группа диагнозов
					left join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
					left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
				WHERE
					es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
					and d2.Diag_pid = :Diag_pid
					and YEAR(ISNULL(es.EvnSection_disDate, es.EvnSection_setDate)) = 2017
					and es.HTMedicalCareClass_id is null
					and (isnull(lsp.LpuSectionProfile_Code,'') not in ('1040', '2040', '3040', '1087', '2087', '3087', '1039', '2039', '3039', '1074', '2074', '3074', '1084', '2084', '3084', '1036', '2036'))
				order by
					es.EvnSection_setDate desc
			";

			$result = $this->db->query($query, array(
				'EvnSection_id' => $data['EvnSection_id'],
				'EvnSection_pid' => $data['EvnSection_pid'],
				'Diag_pid' => $Diag_pid
			));

			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['EvnSection_id'])) {
					$data['DiagEvnSection_disDate'] = $resp[0]['EvnSection_disDate'];
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

		// если была группировка по диагнозу из 2014 года, то связки берем на дату последнего движения.
		if (!empty($data['DiagEvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['DiagEvnSection_disDate'];
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

		$needKSG = true;
		if (in_array($data['LpuSectionProfile_Code'], array('4031', '5031', '6031', '4028', '5028', '6028'))) {
			$needKSG = false;
		}

		// 0.	Определение КСГ при политравме
		if ($needKSG && !empty($data['EvnSection_id'])) {
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
								mo.Mes_Code as KSG,
								mo.Mes_id,
								mt.MesTariff_Value as KOEF,
								mt.MesTariff_id,
								mokpg.KPG,
								mokpg.Mes_kid
							from
								v_MesOld mo (nolock)
								left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
								outer apply(
									select top 1
										mo2.Mes_id as Mes_kid,
										mo2.Mes_Code as KPG
									from
										v_MesOld mo2 (nolock)
										inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
									where
										ml.Mes_id = mo.Mes_id
										and mo2.Mes_begDT <= :EvnSection_disDate
										and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								) mokpg
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
		if ($needKSG && !empty($data['EvnSection_id'])) {
			$query = "
				select top 100
					eu.EvnUsluga_id,
					mo.Mes_Code as KSG,
					mo.Mes_id,
					mt.MesTariff_Value as KOEF,
					mt.MesTariff_id,
					mokpg.KPG,
					mokpg.Mes_kid,
					mo.MesType_id
				from v_EvnUsluga eu (nolock)
					inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu (nolock) on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					outer apply(
						select top 1
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code as KPG
						from
							v_MesOld mo2 (nolock)
							inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					) mokpg
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
						or (:Person_AgeDays >= 91 and :Person_AgeDays <= 365 and mu.MesAgeGroup_id = 10)
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
		if ($needKSG && !empty($data['Diag_id'])) {
			$query = "
				select top 1
					d.Diag_Code,
					mo.Mes_Code as KSG,
					mo.Mes_id,
					mt.MesTariff_Value as KOEF,
					mt.MesTariff_id,
					mokpg.KPG,
					mokpg.Mes_kid
				from v_MesOldUslugaComplex mu (nolock)
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					left join v_Diag d (nolock) on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					outer apply(
						select top 1
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code as KPG
						from
							v_MesOld mo2 (nolock)
							inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					) mokpg
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
						or (:Person_AgeDays >= 91 and :Person_AgeDays <= 365 and mu.MesAgeGroup_id = 10)
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
					SELECT top 1
						case
							when ESNBLSP.LpuSectionProfile_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then ESNBLSP.LpuSectionProfile_id
							else lsp.LpuSectionProfile_id end as LpuSectionProfile_id
					FROM
						v_EvnSection es with (nolock)
						left join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
						left join v_LpuSectionProfile lsp (nolock) on ".$filterProfile."
						outer apply(
							select top 1
								esnbls.LpuSectionProfile_id
							from
								v_EvnSectionNarrowBed esnb (nolock)
								inner join v_LpuSection esnbls (nolock) on esnbls.LpuSection_id = esnb.LpuSection_id
							where
								esnb.EvnSectionNarrowBed_pid = es.EvnSection_id
							order by
								esnb.EvnSectionNarrowBed_setDate desc
						) ESNBLSP
					WHERE
						es.EvnSection_id = :EvnSection_id
				", array('EvnSection_id' => $data['EvnSection_id'], 'LpuSectionProfile_id' => $data['LpuSectionProfile_id']));
			}

			// 3.2	Пробуем определить КПГ по профилю отделения
			if (!empty($data['LpuSectionProfile_id'])) {
				$query = "
					select top 1
						mokpg.Mes_Code as KPG,
						mokpg.Mes_id,
						mtkpg.MesTariff_Value as KOEF,
						mtkpg.MesTariff_id
					from MesOld mokpg (nolock)
						left join v_MesTariff mtkpg (nolock) on mtkpg.Mes_id = mokpg.Mes_id -- Коэффициент КПГ
					where
						mokpg.LpuSectionProfile_id = :LpuSectionProfile_id and mokpg.MesType_id IN (4) -- КПГ
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

		$isReanim = false;
		$ReanimSetDate = $data['EvnSection_setDate'];
		$ReanimDisDate = $data['EvnSection_disDate'];

		/**
		 * 1. получаем отделение с текущего движения.
		 */
		$CurrentLpuSection_id = $data['LpuSection_id'];
		$ReanimLpuSection_id = $this->getFirstResultFromQuery("
			SELECT top 1
				case
					when ESNBLSP.LpuSection_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then ESNBLSP.LpuSection_id else null
				end as LpuSection_id
			FROM
				v_EvnSection es with (nolock)
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = :LpuSectionProfile_id
				outer apply(
					select top 1
						esnb.LpuSection_id
					from
						v_EvnSectionNarrowBed esnb (nolock)
					where
						esnb.EvnSectionNarrowBed_pid = es.EvnSection_id
					order by
						esnb.EvnSectionNarrowBed_setDate desc
				) ESNBLSP
			WHERE
				es.EvnSection_id = :EvnSection_id
		", array(
			'EvnSection_id' => $data['EvnSection_id'],
			'LpuSectionProfile_id' => $data['LpuSectionProfile_id']
		));

		if (!empty($ReanimLpuSection_id)) {
			$CurrentLpuSection_id = $ReanimLpuSection_id;
			$isReanim = true;
		}

		/**
		 * 2. получаем все остальные движения данной КВС.
		 */
		$query = "
			SELECT
				es.EvnSection_id,
				convert(varchar(10), es.EvnSection_setDate, 120) as EvnSection_setDate,
				convert(varchar(10), es.EvnSection_disDate, 120) as EvnSection_disDate,
				datediff(DAY, EvnSection_setDate, EvnSection_disDate) as EvnSection_Duration,
				case
					when ESNBLSP.LpuSection_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then 1 else 0
				end as isReanim,
				case
					when ESNBLSP.LpuSection_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then ESNBLSP.LpuSection_id else es.LpuSection_id
				end as LpuSection_id
			FROM
				v_EvnSection es with (nolock)
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = es.LpuSectionProfile_id
				outer apply(
					select top 1
						esnb.LpuSection_id
					from
						v_EvnSectionNarrowBed esnb (nolock)
					where
						esnb.EvnSectionNarrowBed_pid = es.EvnSection_id
					order by
						esnb.EvnSectionNarrowBed_setDate desc
				) ESNBLSP
			WHERE
				es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
			order by
				es.EvnSection_setDate
		";
		$result = $this->db->query($query, array(
			'EvnSection_id' => $data['EvnSection_id'],
			'EvnSection_pid' => $data['EvnSection_pid']
		));

		if (is_object($result)) {
			$resp = $result->result('array');
			/**
			 * 3. ищем движения которые нужно сгруппировать с текущим
			 */
			foreach($resp as $respone) {
				if ($respone['LpuSection_id'] == $CurrentLpuSection_id) {
					if ($respone['isReanim'] == 1) {
						$isReanim = true;
					}
					if (empty($ReanimSetDate) || strtotime($respone['EvnSection_setDate']) < strtotime($ReanimSetDate)) {
						$ReanimSetDate = $respone['EvnSection_setDate'];
					}
					if (empty($ReanimDisDate) || (strtotime($ReanimDisDate) < strtotime($respone['EvnSection_setDate']))) {
						$ReanimDisDate = $respone['EvnSection_setDate'];
					}
					if (!empty($respone['EvnSection_disDate']) && (empty($ReanimDisDate) || (strtotime($ReanimDisDate) < strtotime($respone['EvnSection_disDate'])))) {
						$ReanimDisDate = $respone['EvnSection_disDate'];
					}
				} else if (strtotime($data['EvnSection_setDate']) >= strtotime($respone['EvnSection_setDate'])) {
					// если другое отделение до нашего движения то не считаем его и всё что до него.
					$ReanimSetDate = $respone['EvnSection_setDate'];
				} else {
					// если другое отделение после нашего движения то не считаем его и останавливаем цикл
					break;
				}
			}
		}

		if ($isReanim) {
			$data['EvnSection_setDate'] = $ReanimSetDate;
			$data['EvnSection_disDate'] = $ReanimDisDate;
		}

		if (substr($data['EvnSection_disDate'], 0, 4) >= '2018') {
			// алгоритм с 2018 года
			return $this->loadKSGKPGKOEF2018($data);
		}

		// группировка по диагнозу
		$Diag_pid = $this->getFirstResultFromQuery("
			select top 1
				d2.Diag_pid
			from
				v_Diag d (nolock)
				inner join v_Diag d1 (nolock) on d1.Diag_id = d.Diag_pid -- группа диагнозов
				inner join v_Diag d2 (nolock) on d2.Diag_id = d1.Diag_pid -- ещё группа диагнозов
			where
				d.Diag_id = :Diag_id
		", array(
			'Diag_id' => $data['Diag_id']
		));

		if (!empty($Diag_pid) && empty($data['HTMedicalCareClass_id']) && !in_array($data['LpuSectionProfile_Code'], array('1040', '2040', '3040', '1087', '2087', '3087', '1039', '2039', '3039', '1074', '2074', '3074', '1084', '2084', '3084', '1036', '2036'))) {
			// достаём движения с той же категорией диагнозов, что и в текущем движении
			$query = "
				SELECT top 1
					es.EvnSection_id,
					convert(varchar(10), ISNULL(es.EvnSection_disDate, es.EvnSection_setDate), 120) as EvnSection_disDate
				FROM
					v_EvnSection es with (nolock)
					inner join v_Diag d (nolock) on d.Diag_id = es.Diag_id
					inner join v_Diag d1 (nolock) on d1.Diag_id = d.Diag_pid -- группа диагнозов
					inner join v_Diag d2 (nolock) on d2.Diag_id = d1.Diag_pid -- ещё группа диагнозов
					left join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
					left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
				WHERE
					es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
					and d2.Diag_pid = :Diag_pid
					and YEAR(ISNULL(es.EvnSection_disDate, es.EvnSection_setDate)) = 2018
					and es.HTMedicalCareClass_id is null
					and (isnull(lsp.LpuSectionProfile_Code,'') not in ('1040', '2040', '3040', '1087', '2087', '3087', '1039', '2039', '3039', '1074', '2074', '3074', '1084', '2084', '3084', '1036', '2036'))
				order by
					ISNULL(es.EvnSection_disDate, es.EvnSection_setDate) desc
			";

			$result = $this->db->query($query, array(
				'EvnSection_id' => $data['EvnSection_id'],
				'EvnSection_pid' => $data['EvnSection_pid'],
				'Diag_pid' => $Diag_pid
			));

			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['EvnSection_id'])) {
					$data['DiagEvnSection_disDate'] = $resp[0]['EvnSection_disDate'];
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

		// если была группировка по диагнозу из 2014 года, то связки берем на дату последнего движения.
		if (!empty($data['DiagEvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['DiagEvnSection_disDate'];
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

		$MesAgeGroup5Filter = "or (:Person_Age < 18 and mu.MesAgeGroup_id = 5)";
		$MesAgeGroup6Filter = "or (:Person_Age >= 18 and mu.MesAgeGroup_id = 6)";
		$MesAgeGroup11Filter = "or (:Person_Age < 2 and mu.MesAgeGroup_id = 11)";
		if ($data['BirthToday'] == 1) {
			// если сегодня д.р. то условия другие
			$MesAgeGroup5Filter = "or (:Person_Age <= 18 and mu.MesAgeGroup_id = 5)";
			$MesAgeGroup6Filter = "or (:Person_Age > 18 and mu.MesAgeGroup_id = 6)";
			$MesAgeGroup11Filter = "or (:Person_Age <= 2 and mu.MesAgeGroup_id = 11)";
		}

		$needKSG = true;
		if (in_array($data['LpuSectionProfile_Code'], array('1004', '2004', '4031', '5031', '6031', '4028', '5028', '6028'))) {
			$needKSG = false;
		}

		// 0.	Определение КСГ при политравме
		if ($needKSG && !empty($data['EvnSection_id'])) {
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
								mo.Mes_Code as KSG,
								mo.Mes_id,
								mt.MesTariff_Value as KOEF,
								mt.MesTariff_id,
								mokpg.KPG,
								mokpg.Mes_kid
							from
								v_MesOld mo (nolock)
								left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
								outer apply(
									select top 1
										mo2.Mes_id as Mes_kid,
										mo2.Mes_Code as KPG
									from
										v_MesOld mo2 (nolock)
										inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
									where
										ml.Mes_id = mo.Mes_id
										and mo2.Mes_begDT <= :EvnSection_disDate
										and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								) mokpg
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
		if ($needKSG && !empty($data['EvnSection_id'])) {
			$query = "
				select top 100
					eu.EvnUsluga_id,
					mo.Mes_Code as KSG,
					mo.Mes_id,
					mt.MesTariff_Value as KOEF,
					mt.MesTariff_id,
					mokpg.KPG,
					mokpg.Mes_kid,
					mo.MesType_id
				from v_EvnUsluga eu (nolock)
					inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu (nolock) on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					outer apply(
						select top 1
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code as KPG
						from
							v_MesOld mo2 (nolock)
							inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					) mokpg
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnUsluga_setDT is not null
					and eu.EvnClass_id in (43,22,29,47)
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
						(:Person_Age > 18 and mu.MesAgeGroup_id = 1)
						or (:Person_Age <= 18 and mu.MesAgeGroup_id = 2)
						or (:Person_AgeDays > 28 and mu.MesAgeGroup_id = 3)
						or (:Person_AgeDays <= 28 and mu.MesAgeGroup_id = 4)
						{$MesAgeGroup5Filter}
						{$MesAgeGroup6Filter}
						or (:Person_Age < 8 and mu.MesAgeGroup_id = 7)
						or (:Person_Age >= 8 and mu.MesAgeGroup_id = 8)
						or (:Person_AgeDays >= 29 and :Person_AgeDays <= 90 and mu.MesAgeGroup_id = 9)
						or (:Person_AgeDays >= 91 and :Person_AgeDays <= 365 and mu.MesAgeGroup_id = 10)
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
		if ($needKSG && !empty($data['Diag_id'])) {
			$query = "
				select top 1
					d.Diag_Code,
					mo.Mes_Code as KSG,
					mo.Mes_id,
					mt.MesTariff_Value as KOEF,
					mt.MesTariff_id,
					mokpg.KPG,
					mokpg.Mes_kid
				from v_MesOldUslugaComplex mu (nolock)
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					left join v_Diag d (nolock) on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					outer apply(
						select top 1
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code as KPG
						from
							v_MesOld mo2 (nolock)
							inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					) mokpg
				where
					mu.Diag_id = :Diag_id
					and (mu.UslugaComplex_id is null OR mu.UslugaComplex_id IN (select eu.UslugaComplex_id from v_EvnUsluga eu (nolock) where eu.EvnUsluga_pid = :EvnSection_id and eu.EvnUsluga_setDT is not null and eu.EvnClass_id in (43,22,29,47) and eu.PayType_id = :PayTypeOms_id))
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS (nolock) where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id))
					and (mu.Sex_id = :Sex_id OR Sex_id IS NULL)
					and (
						(:Person_Age > 18 and mu.MesAgeGroup_id = 1)
						or (:Person_Age <= 18 and mu.MesAgeGroup_id = 2)
						or (:Person_AgeDays > 28 and mu.MesAgeGroup_id = 3)
						or (:Person_AgeDays <= 28 and mu.MesAgeGroup_id = 4)
						{$MesAgeGroup5Filter}
						{$MesAgeGroup6Filter}
						or (:Person_Age < 8 and mu.MesAgeGroup_id = 7)
						or (:Person_Age >= 8 and mu.MesAgeGroup_id = 8)
						or (:Person_AgeDays >= 29 and :Person_AgeDays <= 90 and mu.MesAgeGroup_id = 9)
						or (:Person_AgeDays >= 91 and :Person_AgeDays <= 365 and mu.MesAgeGroup_id = 10)
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
					SELECT top 1
						case
							when ESNBLSP.LpuSectionProfile_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then ESNBLSP.LpuSectionProfile_id
							else lsp.LpuSectionProfile_id end as LpuSectionProfile_id
					FROM
						v_EvnSection es with (nolock)
						left join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
						left join v_LpuSectionProfile lsp (nolock) on ".$filterProfile."
						outer apply(
							select top 1
								esnbls.LpuSectionProfile_id
							from
								v_EvnSectionNarrowBed esnb (nolock)
								inner join v_LpuSection esnbls (nolock) on esnbls.LpuSection_id = esnb.LpuSection_id
							where
								esnb.EvnSectionNarrowBed_pid = es.EvnSection_id
							order by
								esnb.EvnSectionNarrowBed_setDate desc
						) ESNBLSP
					WHERE
						es.EvnSection_id = :EvnSection_id
				", array('EvnSection_id' => $data['EvnSection_id'], 'LpuSectionProfile_id' => $data['LpuSectionProfile_id']));
			}

			// 3.2	Пробуем определить КПГ по профилю отделения
			if (!empty($data['LpuSectionProfile_id'])) {
				$query = "
					select top 1
						mokpg.Mes_Code as KPG,
						mokpg.Mes_id,
						mtkpg.MesTariff_Value as KOEF,
						mtkpg.MesTariff_id
					from MesOld mokpg (nolock)
						left join v_MesTariff mtkpg (nolock) on mtkpg.Mes_id = mokpg.Mes_id -- Коэффициент КПГ
					where
						mokpg.LpuSectionProfile_id = :LpuSectionProfile_id and mokpg.MesType_id IN (4) -- КПГ
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

		return $response;
	}

	/**
	 * поиск ксг/кпг/коэф для 2018 года
	 */
	function loadKSGKPGKOEF2018($data) {
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

		$isReanim = false;
		$ReanimSetDate = $data['EvnSection_setDate'];
		$ReanimDisDate = $data['EvnSection_disDate'];

		/**
		 * 1. получаем отделение с текущего движения.
		 */
		$CurrentLpuSection_id = $data['LpuSection_id'];
		$ReanimLpuSection_id = $this->getFirstResultFromQuery("
			SELECT top 1
				case
					when ESNBLSP.LpuSection_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then ESNBLSP.LpuSection_id else null
				end as LpuSection_id
			FROM
				v_EvnSection es with (nolock)
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = :LpuSectionProfile_id
				outer apply(
					select top 1
						esnb.LpuSection_id
					from
						v_EvnSectionNarrowBed esnb (nolock)
					where
						esnb.EvnSectionNarrowBed_pid = es.EvnSection_id
					order by
						esnb.EvnSectionNarrowBed_setDate desc
				) ESNBLSP
			WHERE
				es.EvnSection_id = :EvnSection_id
		", array(
			'EvnSection_id' => $data['EvnSection_id'],
			'LpuSectionProfile_id' => $data['LpuSectionProfile_id']
		));

		if (!empty($ReanimLpuSection_id)) {
			$CurrentLpuSection_id = $ReanimLpuSection_id;
			$isReanim = true;
		}

		/**
		 * 2. получаем все остальные движения данной КВС.
		 */
		$query = "
			SELECT
				es.EvnSection_id,
				convert(varchar(10), es.EvnSection_setDate, 120) as EvnSection_setDate,
				convert(varchar(10), es.EvnSection_disDate, 120) as EvnSection_disDate,
				datediff(DAY, EvnSection_setDate, EvnSection_disDate) as EvnSection_Duration,
				case
					when ESNBLSP.LpuSection_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then 1 else 0
				end as isReanim,
				case
					when ESNBLSP.LpuSection_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then ESNBLSP.LpuSection_id else es.LpuSection_id
				end as LpuSection_id
			FROM
				v_EvnSection es with (nolock)
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = es.LpuSectionProfile_id
				outer apply(
					select top 1
						esnb.LpuSection_id
					from
						v_EvnSectionNarrowBed esnb (nolock)
					where
						esnb.EvnSectionNarrowBed_pid = es.EvnSection_id
					order by
						esnb.EvnSectionNarrowBed_setDate desc
				) ESNBLSP
			WHERE
				es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
			order by
				es.EvnSection_setDate
		";
		$result = $this->db->query($query, array(
			'EvnSection_id' => $data['EvnSection_id'],
			'EvnSection_pid' => $data['EvnSection_pid']
		));

		if (is_object($result)) {
			$resp = $result->result('array');
			/**
			 * 3. ищем движения которые нужно сгруппировать с текущим
			 */
			foreach($resp as $respone) {
				if ($respone['LpuSection_id'] == $CurrentLpuSection_id) {
					if ($respone['isReanim'] == 1) {
						$isReanim = true;
					}
					if (empty($ReanimSetDate) || strtotime($respone['EvnSection_setDate']) < strtotime($ReanimSetDate)) {
						$ReanimSetDate = $respone['EvnSection_setDate'];
					}
					if (empty($ReanimDisDate) || (strtotime($ReanimDisDate) < strtotime($respone['EvnSection_setDate']))) {
						$ReanimDisDate = $respone['EvnSection_setDate'];
					}
					if (!empty($respone['EvnSection_disDate']) && (empty($ReanimDisDate) || (strtotime($ReanimDisDate) < strtotime($respone['EvnSection_disDate'])))) {
						$ReanimDisDate = $respone['EvnSection_disDate'];
					}
				} else if (strtotime($data['EvnSection_setDate']) >= strtotime($respone['EvnSection_setDate'])) {
					// если другое отделение до нашего движения то не считаем его и всё что до него.
					$ReanimSetDate = $respone['EvnSection_setDate'];
				} else {
					// если другое отделение после нашего движения то не считаем его и останавливаем цикл
					break;
				}
			}
		}

		if ($isReanim) {
			$data['EvnSection_setDate'] = $ReanimSetDate;
			$data['EvnSection_disDate'] = $ReanimDisDate;
		}

		if (substr($data['EvnSection_disDate'], 0, 4) >= '2019') {
			// алгоритм с 2019 года
			return $this->loadKSGKPGKOEF2019($data);
		}

		// группировка по диагнозу
		$Diag_pid = $this->getFirstResultFromQuery("
			select top 1
				d2.Diag_pid
			from
				v_Diag d (nolock)
				inner join v_Diag d1 (nolock) on d1.Diag_id = d.Diag_pid -- группа диагнозов
				inner join v_Diag d2 (nolock) on d2.Diag_id = d1.Diag_pid -- ещё группа диагнозов
			where
				d.Diag_id = :Diag_id
		", array(
			'Diag_id' => $data['Diag_id']
		));

		if (!empty($Diag_pid) && empty($data['HTMedicalCareClass_id']) && !in_array($data['LpuSectionProfile_Code'], array('1040', '2040', '3040', '1087', '2087', '3087', '1039', '2039', '3039', '1074', '2074', '3074', '1084', '2084', '3084', '1036', '2036'))) {
			// достаём движения с той же категорией диагнозов, что и в текущем движении
			$query = "
				SELECT top 1
					es.EvnSection_id,
					convert(varchar(10), ISNULL(es.EvnSection_disDate, es.EvnSection_setDate), 120) as EvnSection_disDate
				FROM
					v_EvnSection es with (nolock)
					inner join v_Diag d (nolock) on d.Diag_id = es.Diag_id
					inner join v_Diag d1 (nolock) on d1.Diag_id = d.Diag_pid -- группа диагнозов
					inner join v_Diag d2 (nolock) on d2.Diag_id = d1.Diag_pid -- ещё группа диагнозов
					left join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
					left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
				WHERE
					es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
					and d2.Diag_pid = :Diag_pid
					and YEAR(ISNULL(es.EvnSection_disDate, es.EvnSection_setDate)) = 2019
					and es.HTMedicalCareClass_id is null
					and (isnull(lsp.LpuSectionProfile_Code,'') not in ('1040', '2040', '3040', '1087', '2087', '3087', '1039', '2039', '3039', '1074', '2074', '3074', '1084', '2084', '3084', '1036', '2036'))
				order by
					ISNULL(es.EvnSection_disDate, es.EvnSection_setDate) desc
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

		// если была группировка по диагнозу из 2014 года, то связки берем на дату последнего движения.
		if (!empty($data['DiagEvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['DiagEvnSection_disDate'];
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

		$MesAgeGroup5Filter = "or (:Person_Age < 18 and mu.MesAgeGroup_id = 5)";
		$MesAgeGroup6Filter = "or (:Person_Age >= 18 and mu.MesAgeGroup_id = 6)";
		$MesAgeGroup11Filter = "or (:Person_Age < 2 and mu.MesAgeGroup_id = 11)";
		if ($data['BirthToday'] == 1) {
			// если сегодня д.р. то условия другие
			$MesAgeGroup5Filter = "or (:Person_Age <= 18 and mu.MesAgeGroup_id = 5)";
			$MesAgeGroup6Filter = "or (:Person_Age > 18 and mu.MesAgeGroup_id = 6)";
			$MesAgeGroup11Filter = "or (:Person_Age <= 2 and mu.MesAgeGroup_id = 11)";
		}

		$needKSG = true;
		if (in_array($data['LpuSectionProfile_Code'], array('1004', '1054', '2004', '2054', '4031', '5031', '6031', '4028', '5028', '6028'))) {
			$needKSG = false;
		}

		// 0.	Определение КСГ при политравме
		if ($needKSG && !empty($data['EvnSection_id'])) {
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
								mo.Mes_Code as KSG,
								mo.Mes_id,
								mt.MesTariff_Value as KOEF,
								mt.MesTariff_id,
								mokpg.KPG,
								mokpg.Mes_kid
							from
								v_MesOld mo (nolock)
								left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
								outer apply(
									select top 1
										mo2.Mes_id as Mes_kid,
										mo2.Mes_Code as KPG
									from
										v_MesOld mo2 (nolock)
										inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
									where
										ml.Mes_id = mo.Mes_id
										and mo2.Mes_begDT <= :EvnSection_disDate
										and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								) mokpg
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

		$KSGOperArray = array();
		// 1. Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
		if ($needKSG && !empty($data['EvnSection_id'])) {
			$query = "
				select top 100
					eu.EvnUsluga_id,
					mu.MesOldUslugaComplex_id,
					mo.Mes_Code as KSG,
					mo.Mes_id,
					mt.MesTariff_Value as KOEF,
					mt.MesTariff_id,
					mokpg.KPG,
					mokpg.Mes_kid,
					mo.MesType_id
				from v_EvnUsluga eu (nolock)
					inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu (nolock) on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					outer apply(
						select top 1
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code as KPG
						from
							v_MesOld mo2 (nolock)
							inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					) mokpg
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnUsluga_setDT is not null
					and eu.EvnClass_id in (43,22,29,47)
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
						(:Person_Age > 18 and mu.MesAgeGroup_id = 1)
						or (:Person_Age <= 18 and mu.MesAgeGroup_id = 2)
						or (:Person_AgeDays > 28 and mu.MesAgeGroup_id = 3)
						or (:Person_AgeDays <= 28 and mu.MesAgeGroup_id = 4)
						{$MesAgeGroup5Filter}
						{$MesAgeGroup6Filter}
						or (:Person_Age < 8 and mu.MesAgeGroup_id = 7)
						or (:Person_Age >= 8 and mu.MesAgeGroup_id = 8)
						or (:Person_AgeDays >= 29 and :Person_AgeDays <= 90 and mu.MesAgeGroup_id = 9)
						or (:Person_AgeDays >= 91 and :Person_AgeDays <= 365 and mu.MesAgeGroup_id = 10)
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
					// ищем максимальную КСГ среди разных услуг.
					foreach ($KSGOperArray as $KSGOperOne) {
						if (empty($CurUsluga) || $CurUsluga != $KSGOperOne['EvnUsluga_id']) {
							$CurUsluga = $KSGOperOne['EvnUsluga_id'];

							if (!empty($KSGOper)) {
								if ($KSGOperOne['KOEF'] > $KSGOper['KOEF']) {
									// берём только если нет связки в MesLink
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
						}
					}
				}
			}
		}

		// 2. Пробуем определить КСГ по наличию диагноза, иначе
		if ($needKSG && !empty($data['Diag_id'])) {
			$query = "
				select top 1
					d.Diag_Code,
					mu.MesOldUslugaComplex_id,
					mo.Mes_Code as KSG,
					mo.Mes_id,
					mt.MesTariff_Value as KOEF,
					mt.MesTariff_id,
					mokpg.KPG,
					mokpg.Mes_kid
				from v_MesOldUslugaComplex mu (nolock)
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					left join v_Diag d (nolock) on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					outer apply(
						select top 1
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code as KPG
						from
							v_MesOld mo2 (nolock)
							inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					) mokpg
				where
					mu.Diag_id = :Diag_id
					and (mu.UslugaComplex_id is null OR mu.UslugaComplex_id IN (select eu.UslugaComplex_id from v_EvnUsluga eu (nolock) where eu.EvnUsluga_pid = :EvnSection_id and eu.EvnUsluga_setDT is not null and eu.EvnClass_id in (43,22,29,47) and eu.PayType_id = :PayTypeOms_id))
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS (nolock) where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id))
					and (mu.Sex_id = :Sex_id OR Sex_id IS NULL)
					and (
						(:Person_Age > 18 and mu.MesAgeGroup_id = 1)
						or (:Person_Age <= 18 and mu.MesAgeGroup_id = 2)
						or (:Person_AgeDays > 28 and mu.MesAgeGroup_id = 3)
						or (:Person_AgeDays <= 28 and mu.MesAgeGroup_id = 4)
						{$MesAgeGroup5Filter}
						{$MesAgeGroup6Filter}
						or (:Person_Age < 8 and mu.MesAgeGroup_id = 7)
						or (:Person_Age >= 8 and mu.MesAgeGroup_id = 8)
						or (:Person_AgeDays >= 29 and :Person_AgeDays <= 90 and mu.MesAgeGroup_id = 9)
						or (:Person_AgeDays >= 91 and :Person_AgeDays <= 365 and mu.MesAgeGroup_id = 10)
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
					SELECT top 1
						case
							when ESNBLSP.LpuSectionProfile_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then ESNBLSP.LpuSectionProfile_id
							else lsp.LpuSectionProfile_id end as LpuSectionProfile_id
					FROM
						v_EvnSection es with (nolock)
						left join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
						left join v_LpuSectionProfile lsp (nolock) on ".$filterProfile."
						outer apply(
							select top 1
								esnbls.LpuSectionProfile_id
							from
								v_EvnSectionNarrowBed esnb (nolock)
								inner join v_LpuSection esnbls (nolock) on esnbls.LpuSection_id = esnb.LpuSection_id
							where
								esnb.EvnSectionNarrowBed_pid = es.EvnSection_id
							order by
								esnb.EvnSectionNarrowBed_setDate desc
						) ESNBLSP
					WHERE
						es.EvnSection_id = :EvnSection_id
				", array('EvnSection_id' => $data['EvnSection_id'], 'LpuSectionProfile_id' => $data['LpuSectionProfile_id']));
			}

			// 3.2	Пробуем определить КПГ по профилю отделения
			if (!empty($data['LpuSectionProfile_id'])) {
				$query = "
					select top 1
						mokpg.Mes_Code as KPG,
						mokpg.Mes_id,
						mtkpg.MesTariff_Value as KOEF,
						mtkpg.MesTariff_id
					from MesOld mokpg (nolock)
						left join v_MesTariff mtkpg (nolock) on mtkpg.Mes_id = mokpg.Mes_id -- Коэффициент КПГ
					where
						mokpg.LpuSectionProfile_id = :LpuSectionProfile_id and mokpg.MesType_id IN (4) -- КПГ
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
			if ($KSGOper['KOEF'] > $KSGTerr['KOEF'] || !empty($data['MesLink_id'])) {
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

		return $response;
	}

	/**
	 * поиск ксг/кпг/коэф для 2019 года
	 */
	function loadKSGKPGKOEF2019($data) {
		if (empty($data['EvnSection_setDate'])) {
			$data['EvnSection_setDate'] = date('Y-m-d');
		}

		if (empty($data['EvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['EvnSection_setDate'];
		}

		$doNotGroupByDiag = false;
		$isReanim = false;
		$isLpuSectionGroup = false;
		$hasParturition = false;
		$ReanimSetDate = $data['EvnSection_setDate'];
		$ReanimDisDate = $data['EvnSection_disDate'];
		$ReanimFilterEvnSection_ids = [];
		$LpuSection_ids = [];
		if (in_array($data['LpuSectionProfile_Code'], ['1041', '2041', '3041'])) {
			// считаем длительность пребывания
			$datediff = strtotime($data['EvnSection_disDate']) - strtotime($data['EvnSection_setDate']);
			$Duration = floor($datediff / (60 * 60 * 24));

			// получаем код диагноза
			$Diag_Code = $this->getFirstResultFromQuery("select top 1 Diag_Code from v_Diag with (nolock) where Diag_id = :Diag_id", ['Diag_id' => $data['Diag_id']]);
			// получаем КСГ движения
			$MesOld_Num = $this->getFirstResultFromQuery("
				select top 1 mo.MesOld_Num from v_EvnSection es (nolock) 
				left join v_MesTariff mt (nolock) on mt.MesTariff_id = es.MesTariff_id
				left join v_MesOld mo (nolock) on mo.Mes_id = mt.Mes_id 
				where es.EvnSection_id = :EvnSection_id",
			['EvnSection_id' => $data['EvnSection_id']]);
			
			$hasParturition = (
				!empty($Diag_Code)
				&& $MesOld_Num == 'st02.001'
				&& (
					($Duration >= 6)
					|| ($Duration >= 2 && in_array($Diag_Code, ['O14.1','O34.2','O36.3','O36.4','O42.2']))
				)
			);
		}

		/**
		 * 1. получаем отделение с текущего движения.
		 */
		$CurrentLpuSection_id = $data['LpuSection_id'];
		$ReanimLpuSection_id = $this->getFirstResultFromQuery("
			SELECT top 1
				case
					when ESNBLSP.LpuSection_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then ESNBLSP.LpuSection_id else null
				end as LpuSection_id
			FROM
				v_EvnSection es with (nolock)
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = :LpuSectionProfile_id
				outer apply(
					select top 1
						esnb.LpuSection_id
					from
						v_EvnSectionNarrowBed esnb (nolock)
					where
						esnb.EvnSectionNarrowBed_pid = es.EvnSection_id
					order by
						esnb.EvnSectionNarrowBed_setDate desc
				) ESNBLSP
			WHERE
				es.EvnSection_id = :EvnSection_id
		", array(
			'EvnSection_id' => $data['EvnSection_id'],
			'LpuSectionProfile_id' => $data['LpuSectionProfile_id']
		));

		if (!empty($ReanimLpuSection_id)) {
			$CurrentLpuSection_id = $ReanimLpuSection_id;
			$isReanim = true;
		}

		if(!empty($data['HTMedicalCareClass_id'])) {
			$LpuSection_ids[] = $data['LpuSection_id'];
		}
		
		/**
		 * 2. получаем все остальные движения данной КВС.
		 */
		$query = "
			SELECT
				es.EvnSection_id,
				convert(varchar(10), es.EvnSection_setDate, 120) as EvnSection_setDate,
				convert(varchar(10), es.EvnSection_disDate, 120) as EvnSection_disDate,
				datediff(DAY, EvnSection_setDate, EvnSection_disDate) as EvnSection_Duration,
				mo.MesOld_Num,
				es.HTMedicalCareClass_id,
				case
					when ESNBLSP.LpuSection_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then 1 else 0
				end as isReanim,
				case
					when ESNBLSP.LpuSection_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then ESNBLSP.LpuSection_id else es.LpuSection_id
				end as LpuSection_id
			FROM
				v_EvnSection es with (nolock)
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = es.LpuSectionProfile_id
				left join v_MesTariff mt (nolock) on mt.MesTariff_id = es.MesTariff_id
				left join v_MesOld mo (nolock) on mo.Mes_id = mt.Mes_id
				outer apply(
					select top 1
						esnb.LpuSection_id
					from
						v_EvnSectionNarrowBed esnb (nolock)
					where
						esnb.EvnSectionNarrowBed_pid = es.EvnSection_id
					order by
						esnb.EvnSectionNarrowBed_setDate desc
				) ESNBLSP
			WHERE
				es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
				and (isnull(lsp.LpuSectionProfile_Code,'') not in ('1040', '2040', '3040', '1087', '2087', '3087', '1039', '2039', '3039', '1074', '2074', '3074', '1084', '2084', '3084', '1036', '2036'))
			order by
				es.EvnSection_setDate
		";
		$result = $this->db->query($query, array(
			'EvnSection_id' => $data['EvnSection_id'],
			'EvnSection_pid' => $data['EvnSection_pid']
		));

		if (is_object($result)) {
			$resp = $result->result('array');
			/**
			 * 3. ищем движения которые нужно сгруппировать с текущим
			 */
			// Достаем отделения всех движений с методом ВМП
			foreach($resp as $respone) {
				if(!empty($respone['HTMedicalCareClass_id'])){
					$LpuSection_ids[] = $respone['LpuSection_id'];
				}
			}
			
			foreach($resp as $respone) {
				
				if ($respone['LpuSection_id'] == $CurrentLpuSection_id && in_array($respone['LpuSection_id'], $LpuSection_ids)) {
					if ($respone['isReanim'] == 1) {
						$isLpuSectionGroup = true;
						$isReanim = true;
					}
					if (empty($ReanimSetDate) || strtotime($respone['EvnSection_setDate']) < strtotime($ReanimSetDate)) {
						$ReanimSetDate = $respone['EvnSection_setDate'];
					}
					if (empty($ReanimDisDate) || (strtotime($ReanimDisDate) < strtotime($respone['EvnSection_setDate']))) {
						$ReanimDisDate = $respone['EvnSection_setDate'];
					}
					if (!empty($respone['EvnSection_disDate']) && (empty($ReanimDisDate) || (strtotime($ReanimDisDate) < strtotime($respone['EvnSection_disDate'])))) {
						$ReanimDisDate = $respone['EvnSection_disDate'];
					}
				} else if (strtotime($data['EvnSection_setDate']) >= strtotime($respone['EvnSection_setDate'])) {
					// если другое отделение до нашего движения то не считаем его и всё что до него.
					$ReanimSetDate = $respone['EvnSection_setDate'];
				} else {
					// если другое отделение после нашего движения то не считаем его и останавливаем цикл
					break;
				}
			}
			
			// •	Если два подряд идущих движения удовлетворяют условиям:
			//Первое движение удовлетворяет хотя бы одному из условий:
			//o	КСГ движения = «st02.001  Осложнения, связанные с беременностью» И длительность движения больше или равна 6 дней;
			//o	КСГ движения = «st02.001  Осложнения, связанные с беременностью» И длительность движения больше или равна 2 дня, И основной диагноз один из O14.1, O34.2, O36.3, O36.4, O42.2.
			//    Второе движение удовлетворяет хотя бы одному из условий:
			//o	КСГ движения = «st02.003 Родоразрешение»;
			//o	КСГ движения = «st02.004 Кесарево сечение»,
			//То такие движения не группируются между собой
			foreach($resp as $respone) {
				if ($hasParturition && in_array($respone['MesOld_Num'],['st02.003','st02.004'])) {
					$doNotGroupByDiag = true;
				}
				// И сформируем группу по реанимации для исключения ее из группировки по диагнозу
				if (in_array($respone['LpuSection_id'], $LpuSection_ids)) {
					$ReanimFilterEvnSection_ids[] = $respone['EvnSection_id'];
				}
			}
		}

		if ($isReanim) {
			$data['EvnSection_setDate'] = $ReanimSetDate;
			$data['EvnSection_disDate'] = $ReanimDisDate;
		}

		// группировка по диагнозу
		if ($doNotGroupByDiag === false) {
			$Diag_pid = $this->getFirstResultFromQuery("
				select top 1
					d2.Diag_pid
				from
					v_Diag d (nolock)
					inner join v_Diag d1 (nolock) on d1.Diag_id = d.Diag_pid -- группа диагнозов
					inner join v_Diag d2 (nolock) on d2.Diag_id = d1.Diag_pid -- ещё группа диагнозов
				where
					d.Diag_id = :Diag_id
			", array(
				'Diag_id' => $data['Diag_id']
			));

			if (!empty($Diag_pid) && empty($data['HTMedicalCareClass_id']) && !in_array($data['LpuSectionProfile_Code'], array('1040', '2040', '3040', '1087', '2087', '3087', '1039', '2039', '3039', '1074', '2074', '3074', '1084', '2084', '3084', '1036', '2036'))) {
				// достаём движения с той же категорией диагнозов, что и в текущем движении
				$query = "
					SELECT top 1
						es.EvnSection_id,
						convert(varchar(10), ISNULL(es.EvnSection_disDate, es.EvnSection_setDate), 120) as EvnSection_disDate
					FROM
						v_EvnSection es with (nolock)
						inner join v_Diag d (nolock) on d.Diag_id = es.Diag_id
						inner join v_Diag d1 (nolock) on d1.Diag_id = d.Diag_pid -- группа диагнозов
						inner join v_Diag d2 (nolock) on d2.Diag_id = d1.Diag_pid -- ещё группа диагнозов
						left join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
						left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
						outer apply(
							select top 1
								esnb.LpuSection_id
							from
								v_EvnSectionNarrowBed esnb (nolock)
							where
								esnb.EvnSectionNarrowBed_pid = es.EvnSection_id
							order by
								esnb.EvnSectionNarrowBed_setDate desc
						) ESNBLSP
					WHERE
						es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
						and d2.Diag_pid = :Diag_pid
						and es.HTMedicalCareClass_id is null
						and (isnull(lsp.LpuSectionProfile_Code,'') not in ('1040', '2040', '3040', '1087', '2087', '3087', '1039', '2039', '3039', '1074', '2074', '3074', '1084', '2084', '3084', '1036', '2036'))
						and (es.EvnSection_id not in ('" . implode("','", $ReanimFilterEvnSection_ids) . "'))
					order by
						ISNULL(es.EvnSection_disDate, es.EvnSection_setDate) desc
				";

				$result = $this->db->query($query, array(
					'EvnSection_id' => $data['EvnSection_id'],
					'EvnSection_pid' => $data['EvnSection_pid'],
					'Diag_pid' => $Diag_pid
				));

				if (is_object($result)) {
					$resp = $result->result('array');
					if (!$isLpuSectionGroup && !empty($resp[0]['EvnSection_disDate']) && $resp[0]['EvnSection_disDate'] > $data['EvnSection_disDate']) {
						$data['LastEvnSection_disDate'] = $resp[0]['EvnSection_disDate'];
					}
				}
			}
		}

		// Используем общий алгоритм
		$this->load->model('MesOldUslugaComplex_model');
		$response = array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'Mes_Code' => '', 'success' => true);

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

			$ReanimEvnSectionIds = array();
			$isReanim = false;

			/**
			 * 1. получаем отделение с текущего движения.
			 */
			$CurrentLpuSection_id = $this->LpuSection_id;
			$ReanimLpuSection_id = $this->getFirstResultFromQuery("
				SELECT top 1
					case
						when ESNBLSP.LpuSection_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then ESNBLSP.LpuSection_id else null
					end as LpuSection_id
				FROM
					v_EvnSection es with (nolock)
					left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = :LpuSectionProfile_id
					outer apply(
						select top 1
							esnb.LpuSection_id
						from
							v_EvnSectionNarrowBed esnb (nolock)
						where
							esnb.EvnSectionNarrowBed_pid = es.EvnSection_id
						order by
							esnb.EvnSectionNarrowBed_setDate desc
					) ESNBLSP
				WHERE
					es.EvnSection_id = :EvnSection_id
			", array(
				'EvnSection_id' => $this->id,
				'LpuSectionProfile_id' => $this->lpuSectionProfileId
			));

			if (!empty($ReanimLpuSection_id)) {
				$CurrentLpuSection_id = $ReanimLpuSection_id;
				$isReanim = true;
			}

			/**
			 * 2. получаем все остальные движения данной КВС.
			 */
			$query = "
				SELECT
					es.EvnSection_id,
					convert(varchar(10), es.EvnSection_setDate, 104) as EvnSection_setDate,
					datediff(DAY, EvnSection_setDate, EvnSection_disDate) as EvnSection_Duration,
					case
						when ESNBLSP.LpuSection_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then 1 else 0
					end as isReanim,
					case
						when ESNBLSP.LpuSection_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then ESNBLSP.LpuSection_id else es.LpuSection_id
					end as LpuSection_id
				FROM
					v_EvnSection es with (nolock)
					left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = es.LpuSectionProfile_id
					outer apply(
						select top 1
							esnb.LpuSection_id
						from
							v_EvnSectionNarrowBed esnb (nolock)
						where
							esnb.EvnSectionNarrowBed_pid = es.EvnSection_id
						order by
							esnb.EvnSectionNarrowBed_setDate desc
					) ESNBLSP
				WHERE
					es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
				order by
					es.EvnSection_setDate
			";
			$result = $this->db->query($query, array(
				'EvnSection_id' => $this->id,
				'EvnSection_pid' => $this->pid
			));

			if (is_object($result)) {
				$resp = $result->result('array');
				/**
				 * 3. ищем движения которые нужно сгруппировать с текущим
				 */
				foreach($resp as $respone) {
					if ($respone['LpuSection_id'] == $CurrentLpuSection_id) {
						if ($respone['isReanim'] == 1) {
							$isReanim = true;
						}
						$ReanimEvnSectionIds[] = $respone['EvnSection_id'];
					} else if (strtotime($this->setDate) >= strtotime($respone['EvnSection_setDate'])) {
						// если другое отделение до нашего движения то не считаем его и всё что до него.
						$ReanimEvnSectionIds = array();
					} else {
						// если другое отделение после нашего движения то не считаем его и останавливаем цикл
						break;
					}
				}
			}

			if ($isReanim) {
				// пересчитываем КСГ
				foreach ($ReanimEvnSectionIds as $ReanimEvnSectionId) {
					$this->es_model->reset();
					$this->es_model->recalcKSGKPGKOEF($ReanimEvnSectionId, $this->sessionParams);
				}
			}

			// группировка по диагнозу
			$Diag_pid = $this->getFirstResultFromQuery("
				select top 1
					d2.Diag_pid
				from
					v_Diag d (nolock)
					inner join v_Diag d1 (nolock) on d1.Diag_id = d.Diag_pid -- группа диагнозов
					inner join v_Diag d2 (nolock) on d2.Diag_id = d1.Diag_pid -- ещё группа диагнозов
				where
					d.Diag_id = :Diag_id
			", array(
				'Diag_id' => $this->Diag_id
			));

			if (!empty($Diag_pid) /*&& empty($this->HTMedicalCareClass_id)*/ && !in_array($this->lpuSectionProfileCode, array('1040', '2040', '3040', '1087', '2087', '3087', '1039', '2039', '3039', '1074', '2074', '3074', '1084', '2084', '3084', '1036', '2036'))) {
				// достаём движения с той же категорией диагнозов, что и в текущем движении
				$query = "
					SELECT
						es.EvnSection_id,
						datediff(DAY, EvnSection_setDate, EvnSection_disDate) as EvnSection_Duration,
						d.Diag_Code,
						lsp.LpuSectionProfile_Code,
						mo.MesOld_Num
					FROM
						v_EvnSection es with (nolock)
						inner join v_Diag d (nolock) on d.Diag_id = es.Diag_id
						inner join v_Diag d1 (nolock) on d1.Diag_id = d.Diag_pid -- группа диагнозов
						inner join v_Diag d2 (nolock) on d2.Diag_id = d1.Diag_pid -- ещё группа диагнозов
						left join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
						left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
						left join v_MesTariff mt (nolock) on mt.MesTariff_id = es.MesTariff_id
						left join v_MesOld mo (nolock) on mo.Mes_id = mt.Mes_id
					WHERE
						es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
						and d2.Diag_pid = :Diag_pid
						and es.HTMedicalCareClass_id is null
						and (isnull(lsp.LpuSectionProfile_Code,'') not in ('1040', '2040', '3040', '1087', '2087', '3087', '1039', '2039', '3039', '1074', '2074', '3074', '1084', '2084', '3084', '1036', '2036'))
					order by
						es.EvnSection_setDate
				";

				$result = $this->db->query($query, array(
					'EvnSection_id' => $this->id,
					'EvnSection_pid' => $this->pid,
					'Diag_pid' => $Diag_pid
				));

				if (is_object($result)) {
					$resp = $result->result('array');
					foreach ($resp as $respone) {
						// @task https://redmine.swan-it.ru/issues/194821
						if (
							in_array($respone['LpuSectionProfile_Code'], ['1041', '2041', '3041'])
							&& $respone['MesOld_Num'] == 'st02.001'
							&& (
								($respone['EvnSection_Duration'] >= 6 )
								|| ($respone['EvnSection_Duration'] >= 2 && in_array($respone['Diag_Code'], ['O14.1','O34.2','O36.3','O36.4','O42.2']))
							)
						) {
							continue;
						}

						// пересчитываем КСГ
						$this->es_model->reset();
						$this->es_model->recalcKSGKPGKOEF($respone['EvnSection_id'], $this->sessionParams);
					}
				}
			}
		}
	}
}
