<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnSection
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 */

require_once(APPPATH . 'models/_pgsql/EvnSection_model.php');

class Khak_EvnSection_model extends EvnSection_model
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * поиск ксг/кпг/коэф для 2019 года
	 */
	function loadKSGKPGKOEF2019($data)
	{
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
			$response['UslugaComplex_id'] = $resp['UslugaComplex_id'];
		}
		return $response;
	}

	/**
	 * поиск ксг/коэф
	 */
	function loadKSGKPGKOEF($data)
	{
		$KSGFromPolyTrauma = false;
		$KSGOper = false;
		$KSGTerr = false;
		$KPGFromLpuSectionProfile = false;

		$data['PayTypeOms_id'] = $this->getFirstResultFromQuery("select PayType_id as \"PayType_id\" from v_PayType pt where pt.PayType_SysNick = 'oms' limit 1");
		if (empty($data['PayTypeOms_id'])) {
			return array('Error_Msg' => 'Ошибка получения идентификатора вида оплаты ОМС');
		}

		if (empty($data['EvnSection_setDate'])) {
			$data['EvnSection_setDate'] = date('Y-m-d');
		}

		if (empty($data['EvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['EvnSection_setDate'];
		}

		if (strtotime($data['EvnSection_disDate']) >= strtotime('01.05.2019')) {
			// алгоритм с 2019 года
			return $this->loadKSGKPGKOEF2019($data);
		}

		// достаём дату последнего движения
		$query = "
			SELECT
				es.EvnSection_id as \"EvnSection_id\",
				to_char(cast(COALESCE(es.EvnSection_disDate, es.EvnSection_setDate) as date), 'yyyy-mm-dd') as \"EvnSection_disDate\"
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
				if ($data['LastEvnSection_disDate'] >= '2019-05-01') {
					return $this->loadKSGKPGKOEF2019($data);
				}
			}
		}

		// считаем длительность пребывания
		$datediff = strtotime($data['EvnSection_disDate']) - strtotime($data['EvnSection_setDate']);
		$data['Duration'] = floor($datediff / (60 * 60 * 24));
		if (in_array($data['LpuUnitType_id'], array('6', '7', '9'))) {
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
					when EXTRACT(MONTH FROM PS.Person_BirthDay) = EXTRACT(MONTH FROM :EvnSection_setDate) and EXTRACT(DAY FROM PS.Person_BirthDay) = EXTRACT(DAY FROM :EvnSection_setDate) then 1 else 0
				end as BirthToday
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
					if ($resp[0]['PolyTrauma_Code'] == 7 || !empty($resp[0]['EvnDiagPS_id'])) {
						$query = "
							select
								mo.Mes_Code as \"KSG\",
								mo.Mes_id as \"Mes_id\",
								mt.MesTariff_Value as \"KOEF\",
								mt.MesTariff_id as \"MesTariff_id\",
								mul.UslugaComplex_id as \"UslugaComplex_id\"
							from
								v_MesOld mo
								left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
								left join lateral (
									select UslugaComplex_id
									from v_UslugaComplex
									where UslugaComplex_Code = '066078'
									and UslugaComplex_begDT <= :EvnSection_disDate
									and COALESCE(UslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate
									limit 1
								) mul on true
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
		if (in_array($data['LpuUnitType_id'], array('6', '7', '9'))) {
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
		if (!empty($data['EvnSection_id'])) {
			$query = "
				select
					mu.MesOldUslugaComplex_id as \"MesOldUslugaComplex_id\",
					eu.EvnUsluga_id as \"EvnUsluga_id\",
					mo.Mes_Code as \"KSG\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"KOEF\",
					mt.MesTariff_id as \"MesTariff_id\",
					mo.MesType_id as \"MesType_id\",
					mul.UslugaComplex_id as \"UslugaComplex_id\",
					mu.MesOldUslugaComplex_DurationTo as \"MesOldUslugaComplex_DurationTo\" 
				from v_EvnUsluga eu
					inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					left join lateral (
						select UslugaComplex_id
						from v_MesOldUslugaComplex
						where MesOldUslugaComplex_id = mu.MesOldUslugaComplex_id
						and MesOldUslugaComplex_begDT <= :EvnSection_disDate
						and COALESCE(MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate
						limit 1
					) mul on true
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
					and mo.MesType_id IN (2,3,5,9,10)
					and COALESCE(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				order by
					eu.EvnUsluga_id,
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mo.MesType_id is not null then 1 else 0 end
					+ case when mt.MesPayType_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.Diag_nid is not null then 1 else 0 end
					+ case when mu.Sex_id is not null then 1 else 0 end
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
					mu.MesOldUslugaComplex_id as \"MesOldUslugaComplex_id\",
					d.Diag_Code as \"Diag_Code\",
					mo.Mes_Code as \"KSG\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"KOEF\",
					mt.MesTariff_id as \"MesTariff_id\",
					mul.UslugaComplex_id as \"UslugaComplex_id\"
				from v_MesOldUslugaComplex mu
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					left join v_Diag d on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					left join lateral (
						select UslugaComplex_id UslugaComplex_id
						from v_MesOldUslugaComplex
						where MesOldUslugaComplex_id = mu.MesOldUslugaComplex_id
						and MesOldUslugaComplex_begDT <= :EvnSection_disDate
						and COALESCE(MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate
						limit 1
					) mul on true
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
					and mo.MesType_id IN (2,3,5,9,10)
					and COALESCE(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				order by
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mo.MesType_id is not null then 1 else 0 end
					+ case when mt.MesPayType_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.Diag_nid is not null then 1 else 0 end
					+ case when mu.Sex_id is not null then 1 else 0 end
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

		$response = array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'EvnSection_CoeffCTP' => '', 'UslugaComplex_id' => null, 'MesOldUslugaComplex_id' => null, 'success' => true);

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
				$response['KOEF'] = $KSGOper['KOEF'];
				$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
				$response['UslugaComplex_id'] = $KSGOper['UslugaComplex_id'];
				$response['MesOldUslugaComplex_id'] = $KSGOper['MesOldUslugaComplex_id'];
			} else {
				$response['KSG'] = $KSGTerr['KSG'];
				$response['KOEF'] = $KSGTerr['KOEF'];
				$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
				$response['UslugaComplex_id'] = $KSGTerr['UslugaComplex_id'];
				$response['MesOldUslugaComplex_id'] = $KSGTerr['MesOldUslugaComplex_id'];
			}
		} else if ($KSGOper) {
			$response['KSG'] = $KSGOper['KSG'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			$response['KOEF'] = $KSGOper['KOEF'];
			$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
			$response['UslugaComplex_id'] = $KSGOper['UslugaComplex_id'];
			$response['MesOldUslugaComplex_id'] = $KSGOper['MesOldUslugaComplex_id'];
		} else if ($KSGTerr) {
			$response['Alert_Code'] = 1; // 'Не найдено КСГ по операциям';
			$response['KSG'] = $KSGTerr['KSG'];
			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['KOEF'] = $KSGTerr['KOEF'];
			$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
			$response['UslugaComplex_id'] = $KSGTerr['UslugaComplex_id'];
			$response['MesOldUslugaComplex_id'] = $KSGTerr['MesOldUslugaComplex_id'];
		} else if ($KPGFromLpuSectionProfile) {
			$response['Alert_Code'] = 2; // 'Не найдено КСГ по операциям и диагнозу';
			$response['KOEF'] = $KPGFromLpuSectionProfile['KOEF'];
			$response['MesTariff_id'] = $KPGFromLpuSectionProfile['MesTariff_id'];
			$response['UslugaComplex_id'] = $KPGFromLpuSectionProfile['UslugaComplex_id'];
		}

		if ($KSGFromPolyTrauma) {
			$response['KSG'] = $KSGFromPolyTrauma['KSG'];
			$response['Mes_tid'] = $KSGFromPolyTrauma['Mes_id'];
			$response['KOEF'] = $KSGFromPolyTrauma['KOEF'];
			$response['MesTariff_id'] = $KSGFromPolyTrauma['MesTariff_id'];
			$response['UslugaComplex_id'] = $KSGFromPolyTrauma['UslugaComplex_id'];
		}

		// определяем КСКП.
		/*
		$EvnSection_CoeffCTP = 0;
		if (!empty($data['EvnSection_id'])) {
			$query = "
				select
					es.EvnSection_CoeffCTP
				from
					v_EvnSection es (nolock)
				where
					 es.EvnSection_id = :EvnSection_id
			";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$EvnSection_CoeffCTP = $resp[0]['EvnSection_CoeffCTP'];
				}
			}
		}

		$response['EvnSection_CoeffCTP'] = round($EvnSection_CoeffCTP, 3);
		*/

		if (!empty($response['KOEF'])) {
			$response['KOEF'] = round($response['KOEF'], 2);
		}

		return $response;
	}

	/**
	 * Пересчёт КСЛП
	 */
	protected function _recalcKSKP()
	{
		// убираем КСЛП с движения
		$query = "
			update
				EvnSection
			set
				EvnSection_CoeffCTP = null
			from
				EvnSection es
				inner join Evn e  on e.Evn_id = es.Evn_id
			where
				e.Evn_id = :EvnSection_id and EvnSection.Evn_id = es.Evn_id
		";
		$this->db->query($query, [
			'EvnSection_id' => $this->id
		]);

		// удаляем все связки КСЛП по движению
		$query = "
			select
				eskl.EvnSectionKSLPLink_id as \"EvnSectionKSLPLink_id\"
			from
				EvnSectionKSLPLink eskl 
				inner join Evn e  on e.Evn_id = eskl.EvnSection_id
			where
				e.Evn_id = :EvnSection_id
		";
		$resp_eskl = $this->queryResult($query, [
			'EvnSection_id' => $this->id
		]);
		foreach($resp_eskl as $one_eskl) {
			$this->db->query("
				select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from p_EvnSectionKSLPLink_del(
					EvnSectionKSLPLink_id := :EvnSectionKSLPLink_id);
			", [
				'EvnSectionKSLPLink_id' => $one_eskl['EvnSectionKSLPLink_id']
			]);
		}

		$resp_es = $this->queryResult("
			select
				es.EvnSection_id as \"EvnSection_id\",
				es.EvnSection_pid as \"EvnSection_pid\",
				to_char(es.EvnSection_setDate, 'YYYY-MM-DD') as \"EvnSection_setDate\",
				to_char(COALESCE(es.EvnSection_disDate, es.EvnSection_setDate), 'YYYY-MM-DD') as \"EvnSection_disDate\",
				lu.LpuUnitType_id as \"LpuUnitType_id\"
			from
				v_EvnSection es 
				inner join v_PayType pt  on pt.PayType_id = es.PayType_id
				left join v_LpuSection ls  on ls.LpuSection_id = es.LpuSection_id
				left join v_LpuUnit lu  on lu.LpuUnit_id = ls.LpuUnit_id
			where
				es.EvnSection_id = :EvnSection_id
				and COALESCE(es.EvnSection_IsPriem, 1) = 1
				and pt.PayType_SysNick = 'oms'
		", [
			'EvnSection_id' => $this->id
		]);

		foreach($resp_es as $respone) {
			$datediff = strtotime($respone['EvnSection_disDate']) - strtotime($respone['EvnSection_setDate']);
			$Duration = floor($datediff/(60*60*24));

			$esdata = [
				'EvnSection_id' => $respone['EvnSection_id'],
				'LpuUnitType_id' => $respone['LpuUnitType_id'],
				'EvnSection_disDate' => $respone['EvnSection_disDate'],
				'Duration' => $Duration
			];

			$kslpData = $this->calcCoeffCTP($esdata);

			// 4. записываем для каждого движения группы полученные КСЛП в БД.
			$query = "
				update
					EvnSection
				set
					EvnSection_CoeffCTP = :EvnSection_CoeffCTP
				where
					EvnSection_id = :EvnSection_id
			";

			$this->db->query($query, [
				'EvnSection_CoeffCTP' => $kslpData['EvnSection_CoeffCTP'],
				'EvnSection_id' => $this->id
			]);

			// и список КСЛП тоже для каждого движения группы refs #136750
			foreach($kslpData['List'] as $one_kslp) {
				$this->db->query("
					select EvnSectionKSLPLink_id as \"EvnSectionKSLPLink_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"		
					from p_EvnSectionKSLPLink_ins(
						EvnSectionKSLPLink_id :=null,
						EvnSection_id := :EvnSection_id,
						EvnSectionKSLPLink_Code := :EvnSectionKSLPLink_Code,
						EvnSectionKSLPLink_Value := :EvnSectionKSLPLink_Value,
						pmUser_id := :pmUser_id);
				", [
					'EvnSection_id' => $this->id,
					'EvnSectionKSLPLink_Code' => $one_kslp['Code'],
					'EvnSectionKSLPLink_Value' => $one_kslp['Value'],
					'pmUser_id' => $this->promedUserId
				]);
			}
		}
	}

	/**
	 * Считаем КСЛП для движения
	 */
	protected function calcCoeffCTP($data) {
		$EvnSection_CoeffCTP = 0;
		$List = [];

		// Достаём коды КСЛП из тарифа "КСЛП"
		$resp_codes = $this->queryResult("
			select
				CODE.AttributeValue_ValueString as \"code\",
				av.AttributeValue_ValueFloat as \"value\"
			from
				v_AttributeVision avis 
				inner join v_AttributeValue av  on av.AttributeVision_id = avis.AttributeVision_id
				LEFT JOIN LATERAL (
					select 
						av2.AttributeValue_ValueString
					from
						v_AttributeValue av2 
						inner join v_Attribute a2  on a2.Attribute_id = av2.Attribute_id
					where
						av2.AttributeValue_rid = av.AttributeValue_id
						and a2.Attribute_SysNick = 'KSLP_CODE'
                    limit 1
				) CODE ON true
			where
				avis.AttributeVision_TableName = 'dbo.TariffClass'
				and avis.AttributeVision_TablePKey = (select TariffClass_id from v_TariffClass  where TariffClass_SysNick = 'kslp' limit 1)
				and avis.AttributeVision_IsKeyValue = 2
				and COALESCE(av.AttributeValue_begDate, CAST(:EvnSection_disDate as date)) <= :EvnSection_disDate
				and COALESCE(av.AttributeValue_endDate, CAST(:EvnSection_disDate as date)) >= :EvnSection_disDate
		", [
			'EvnSection_disDate' => $data['EvnSection_disDate']
		]);

		$KSLPCodes = [];
		foreach($resp_codes as $one_code) {
			$KSLPCodes[$one_code['code']] = floatval($one_code['value']);
		}

		// достаём атрибуты из услуги ЭКО
		$AttributeSignCodes = [];
		if (in_array($data['LpuUnitType_id'], ['6','7','9'])) {
			if (empty($data['PayTypeOms_id'])) {
				$data['PayTypeOms_id'] = $this->getFirstResultFromQuery("select PayType_id  as \"PayType_id\" from v_PayType pt  where pt.PayType_SysNick = 'oms' limit 1");
				if (empty($data['PayTypeOms_id'])) {
					throw new Exception('Ошибка получения идентификатора вида оплаты ОМС', 500);
				}
			}

			$resp_eu = $this->queryResult("
				select
					asign.AttributeSign_Code
				FROM
					v_EvnUsluga eu
					inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_AttributeSignValue asv on asv.AttributeSignValue_TablePKey = eu.EvnUsluga_id
					inner join v_AttributeSign asign on asign.AttributeSign_id = asv.AttributeSign_id and asign.AttributeSign_Code in (7, 8, 9, 10, 11) 
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnClass_id in (43,22,29)
					and uc.UslugaComplex_Code = 'A11.20.017'
			", [
				'EvnSection_id' => $data['EvnSection_id'],
				'PayTypeOms_id' => $data['PayTypeOms_id']
			]);

			foreach ($resp_eu as $one_eu) {
				if (!in_array($one_eu['AttributeSign_Code'], $AttributeSignCodes)) {
					$AttributeSignCodes[] = $one_eu['AttributeSign_Code'];
				}
			}
		}

		// 1. ДС. Проведение первого этапа экстракорпорального оплодотворения (стимуляция суперовуляции)
		$codeKSLP = 1;
		if (in_array($data['LpuUnitType_id'], ['6','7','9']) && isset($KSLPCodes[$codeKSLP])) {
			if (in_array(7, $AttributeSignCodes)) {
				$coeffCTP = $KSLPCodes[$codeKSLP];
				if ($coeffCTP > $EvnSection_CoeffCTP) {
					$List = [[
						'Code' => $codeKSLP,
						'Value' => $coeffCTP
					]];
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 2. ДС. Проведение I – III этапов экстракорпорального оплодотворения
		$codeKSLP = 2;
		if (in_array($data['LpuUnitType_id'], ['6','7','9']) && isset($KSLPCodes[$codeKSLP])) {
			if (
				in_array(7, $AttributeSignCodes)
				&& in_array(8, $AttributeSignCodes)
				&& in_array(9, $AttributeSignCodes)
				&& in_array(11, $AttributeSignCodes)
			) {
				$coeffCTP = $KSLPCodes[$codeKSLP];
				if ($coeffCTP > $EvnSection_CoeffCTP) {
					$List = [[
						'Code' => $codeKSLP,
						'Value' => $coeffCTP
					]];
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 3. ДС. Полный цикл экстракорпорального оплодотворения без применения криоконсервации эмбрионов
		$codeKSLP = 3;
		if (in_array($data['LpuUnitType_id'], ['6','7','9']) && isset($KSLPCodes[$codeKSLP])) {
			if (
				in_array(7, $AttributeSignCodes)
				&& in_array(8, $AttributeSignCodes)
				&& in_array(9, $AttributeSignCodes)
				&& in_array(10, $AttributeSignCodes)
			) {
				$coeffCTP = $KSLPCodes[$codeKSLP];
				if ($coeffCTP > $EvnSection_CoeffCTP) {
					$List = [[
						'Code' => $codeKSLP,
						'Value' => $coeffCTP
					]];
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 4. ДС. Полный цикл экстракорпорального оплодотворения с криоконсервацией эмбрионов
		$codeKSLP = 4;
		if (in_array($data['LpuUnitType_id'], ['6','7','9']) && isset($KSLPCodes[$codeKSLP])) {
			if (
				in_array(7, $AttributeSignCodes)
				&& in_array(8, $AttributeSignCodes)
				&& in_array(9, $AttributeSignCodes)
				&& in_array(10, $AttributeSignCodes)
				&& in_array(11, $AttributeSignCodes)
			) {
				$coeffCTP = $KSLPCodes[$codeKSLP];
				if ($coeffCTP > $EvnSection_CoeffCTP) {
					$List = [[
						'Code' => $codeKSLP,
						'Value' => $coeffCTP
					]];
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 5. ДС. Размораживание криоконсервированных эмбрионов с последующим переносом эмбрионов в полость матки (криоперенос)
		$codeKSLP = 5;
		if (in_array($data['LpuUnitType_id'], ['6','7','9']) && isset($KSLPCodes[$codeKSLP])) {
			if (in_array(10, $AttributeSignCodes)) {
				$coeffCTP = $KSLPCodes[$codeKSLP];
				if ($coeffCTP > $EvnSection_CoeffCTP) {
					$List = [[
						'Code' => $codeKSLP,
						'Value' => $coeffCTP
					]];
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		return [
			'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP,
			'List' => $List
		];
	}
}